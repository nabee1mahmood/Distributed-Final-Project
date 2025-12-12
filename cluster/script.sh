#!/bin/bash
set -euo pipefail

# ============================================================
# CouchDB 3-node cluster on plain Docker (no Minikube)
# - Nodes: couch1, couch2, couch3
# - Shared bridge network: couchnet
# - Uses FQDN hostnames to satisfy Erlang distributed node requirements
# ============================================================

NETWORK="couchnet"
DOMAIN="couchnet.local"          # Fake local domain for FQDNs
COOKIE="MY_SUPER_SECRET_COOKIE"
ADMIN_USER="admin"
ADMIN_PASS="admin"
IMAGE="couchdb:3.3.3"

PORT1=15984
PORT2=25984
PORT3=35984

echo "=== Cleaning previous Docker cluster ==="
docker rm -f couch1 couch2 couch3 >/dev/null 2>&1 || true
docker network rm "$NETWORK" >/dev/null 2>&1 || true

echo "=== Creating Docker network ${NETWORK} ==="
docker network create "$NETWORK" >/dev/null

start_node() {
  NAME="$1"
  HOST_PORT="$2"
  FQDN="${NAME}.${DOMAIN}"
  echo "=== Starting ${NAME} (hostname: ${FQDN}) ==="
  docker run -d \
    --name "${NAME}" \
    --hostname "${FQDN}" \
    --network "${NETWORK}" \
    -p "${HOST_PORT}:5984" \
    -e COUCHDB_USER="${ADMIN_USER}" \
    -e COUCHDB_PASSWORD="${ADMIN_PASS}" \
    -e COUCHDB_ERLANG_COOKIE="${COOKIE}" \
    -e NODENAME="${FQDN}" \
    "${IMAGE}"
}

start_node couch1 "$PORT1"
start_node couch2 "$PORT2"
start_node couch3 "$PORT3"

echo "=== Discovering container IPs ==="
IP1=$(docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' couch1)
IP2=$(docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' couch2)
IP3=$(docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' couch3)

add_hosts() {
  TARGET="$1"
  docker exec "${TARGET}" sh -c "printf '%s %s\n' '${IP1}' 'couch1.${DOMAIN}' >> /etc/hosts"
  docker exec "${TARGET}" sh -c "printf '%s %s\n' '${IP2}' 'couch2.${DOMAIN}' >> /etc/hosts"
  docker exec "${TARGET}" sh -c "printf '%s %s\n' '${IP3}' 'couch3.${DOMAIN}' >> /etc/hosts"
}

add_hosts couch1
add_hosts couch2
add_hosts couch3

wait_http() {
  NAME="$1"
  echo "Waiting for ${NAME} HTTP..."
  for i in $(seq 1 60); do
    if docker exec "${NAME}" curl -fsS http://127.0.0.1:5984/ >/dev/null 2>&1; then
      echo "HTTP up on ${NAME}"
      return 0
    fi
    sleep 2
  done
  echo "ERROR: ${NAME} did not become ready in time."
  docker logs "${NAME}" || true
  exit 1
}

wait_http couch1
wait_http couch2
wait_http couch3

echo "=== Creating system DBs (n=1) on couch1 ==="
for DB in _users _replicator _global_changes; do
  docker exec couch1 curl -fsS -u "${ADMIN_USER}:${ADMIN_PASS}" -X PUT "http://127.0.0.1:5984/${DB}?n=1" >/dev/null 2>&1 || true
done

cluster_status() {
  NAME="$1"
  docker exec "$NAME" curl -fsS -u "${ADMIN_USER}:${ADMIN_PASS}" "http://127.0.0.1:5984/_cluster_setup" 2>/dev/null || true
}

enable_local() {
  NAME="$1"
  echo "=== Enabling cluster locally on ${NAME} ==="
  docker exec "${NAME}" curl -sS -u "${ADMIN_USER}:${ADMIN_PASS}" -X POST "http://127.0.0.1:5984/_cluster_setup" \
    -H 'Content-Type: application/json' \
    -d "{\"action\":\"enable_cluster\",\"username\":\"${ADMIN_USER}\",\"password\":\"${ADMIN_PASS}\",\"bind_address\":\"0.0.0.0\",\"port\":5984}" \
    -w "\nHTTP %{http_code}\n" >/dev/null || true
  echo "Status on ${NAME}: $(cluster_status "${NAME}")"
}

enable_local couch1
enable_local couch2
enable_local couch3

add_node() {
  HOST_FQDN="$1"
  echo "=== Adding ${HOST_FQDN} via couch1 ==="
  docker exec couch1 curl -sS -u "${ADMIN_USER}:${ADMIN_PASS}" -X POST "http://127.0.0.1:5984/_cluster_setup" \
    -H 'Content-Type: application/json' \
    -d "{\"action\":\"add_node\",\"host\":\"${HOST_FQDN}\",\"port\":5984,\"username\":\"${ADMIN_USER}\",\"password\":\"${ADMIN_PASS}\"}" \
    -w "\nHTTP %{http_code}\n" >/dev/null || true
}

add_node "couch2.${DOMAIN}"
add_node "couch3.${DOMAIN}"

echo "=== Finishing cluster via couch1 ==="
docker exec couch1 curl -sS -u "${ADMIN_USER}:${ADMIN_PASS}" -X POST "http://127.0.0.1:5984/_cluster_setup" \
  -H 'Content-Type: application/json' \
  -d '{"action":"finish_cluster"}' \
  -w "\nHTTP %{http_code}\n" >/dev/null || true

echo "=== Cluster membership (couch1) ==="
docker exec couch1 curl -s -u "${ADMIN_USER}:${ADMIN_PASS}" "http://127.0.0.1:5984/_membership" || true

# ============================================================
# CREATE TESTDB AFTER CLUSTER IS FINISHED (n=3 so it shards across nodes)
# ============================================================
echo "=== Creating testdb across cluster (n=3) ==="
docker exec couch1 curl -fsS -u "${ADMIN_USER}:${ADMIN_PASS}" -X PUT "http://127.0.0.1:5984/testdb?n=3" || true

echo "=== Done: 3-node CouchDB cluster on Docker with FQDNs ==="
echo "Access:"
echo " - couch1: http://localhost:${PORT1}"
echo " - couch2: http://localhost:${PORT2}"
echo " - couch3: http://localhost:${PORT3}"
