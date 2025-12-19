# Distributed CouchDB NoSQL Cluster

### Introduction
This was a final project in our distributed system course where we got into groups and  the goal was to create a distributed system using OpenSuSE Linux, and each team was given a NoSQL system and we had to use CRUD operations (Create, Read, Update, and Delete). 


Our team was assigned CouchDB, a distributed NoSQL database designed for high availability, fault tolerance, offline functionality, and multi-master replication. We deployed a multi-node CouchDB cluster using Docker, allowing data to be automatically replicated and sharded across multiple nodes. A frontend application interacts with the cluster through a single entry point, demonstrating a distributed system.


### Tools and Technologies Used
- Docker:
- PouchDB: (used for offline storage)
- NGINX (used for the web server to direct when an operation was requested to send it to the correct container



### Report 
This was our report detailing the step by step to bring the system and the commands required.


📄 **[View the Final Project Report (PDF)](DistributedFinalProject.pdf)**






### Conclusion:
This project demonstrates the implementation of a distributed NoSQL system using CouchDB. A multi-node CouchDB cluster was deployed using Docker, with high availability, horizontal scalability, and multi-master replication. An NGINX web server was configured to act as a single access point and load balancer for the cluster, while a web-based CRUD application was used to interact with the database.
