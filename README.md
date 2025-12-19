# Distributed CouchDB NoSQL Cluster

### Introduction
This project was completed as the final project for our **Distributed Systems** course. We worked in teams to design and deploy a **distributed system on OpenSUSE Linux**, where each team was assigned a **NoSQL database**. The system was required to support full **CRUD operations (Create, Read, Update, and Delete)** through an application interface, demonstrating core distributed systems concepts such as data distribution, replication, and fault tolerance.


Our team was assigned CouchDB, a distributed NoSQL database designed for high availability, fault tolerance, offline functionality, and multi-master replication. We deployed a multi-node CouchDB cluster using Docker, allowing data to be automatically replicated and sharded across multiple nodes. A frontend application interacts with the cluster through a single entry point, demonstrating a distributed system.


### Tools and Technologies Used
- **Docker**  
  Used to containerize CouchDB nodes.

- **CouchDB**  
  A distributed NoSQL database used to store JSON documents with built-in support for replication, sharding, and multi-master architecture.

- **PouchDB**  
  Used for **offline-first storage**, allowing data to be stored locally when offline.

- **NGINX**  
  Used for routing incoming CRUD requests to the appropriate CouchDB node and acting as a single access point for the cluster.

- **OpenSUSE Linux**  
  The primary operating system used for the development and deployment of the distributed system.



### Report 
This was our report detailing the step by step to bring the system and the commands required.


📄 **[View the Final Project Report (PDF)](DistributedFinalProject.pdf)**






### Conclusion:
This project demonstrates the implementation of a distributed NoSQL system using CouchDB. A multi-node CouchDB cluster was deployed using Docker, with high availability, horizontal scalability, and multi-master replication. An NGINX web server was configured to act as a single access point and load balancer for the cluster, while a web-based CRUD application was used to interact with the database.
