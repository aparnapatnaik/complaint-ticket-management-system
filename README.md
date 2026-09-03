# Complaint Ticket Management System

A PHP and MySQL based Complaint Ticket Management System deployed on AWS using Docker, Jenkins CI/CD, and multiple AWS services.

This project demonstrates how a traditional PHP/MySQL application can be transformed into a cloud-based DevOps deployment using containerization, automated deployment, cloud storage, messaging, monitoring, auditing, load balancing, and auto scaling.

## Project Architecture

User
↓
Application Load Balancer
↓
EC2 / Auto Scaling
↓
Docker Container
↓
PHP + Apache Application
↓
Amazon RDS MySQL

Supporting AWS services:

- Amazon S3 - complaint file and attachment storage
- Amazon SQS - asynchronous complaint event messaging
- Amazon CloudWatch - monitoring and system metrics
- AWS CloudTrail - API activity auditing
- AWS IAM - permissions and access control
- Amazon VPC - networking
- Application Load Balancer - traffic distribution
- Auto Scaling - application scalability
- Jenkins - CI/CD automation
- GitHub - source code management
- Docker - application containerization

## Technologies Used

### Application

- PHP
- MySQL
- Bootstrap
- Apache

### AWS

- Amazon EC2
- Amazon RDS
- Amazon S3
- Amazon SQS
- Amazon CloudWatch
- AWS CloudTrail
- AWS IAM
- Amazon VPC
- Application Load Balancer
- Auto Scaling

### DevOps

- Docker
- Docker Compose
- Jenkins
- Git
- GitHub

## AWS Implementation

### Amazon EC2

The application is deployed on an Ubuntu EC2 instance.

The EC2 instance uses an IAM role so that AWS services can be accessed without storing long-term AWS access keys directly in the application.

### Amazon RDS

Amazon RDS is used as the managed MySQL database layer.

This separates the database from the application container and provides a more production-oriented architecture.

### Amazon S3

Amazon S3 is used to store complaint-related files and attachments.

Objects are organized using a structure similar to:

`complaints/<complaint-id>/<file>`

This keeps uploaded files separate from the application server filesystem.

### Amazon SQS

Amazon SQS is used for asynchronous complaint event messaging.

When a complaint is submitted, the application sends an event to the SQS queue.

Queue:

`complaint-events`

This demonstrates decoupled communication between application components.

### Amazon CloudWatch

CloudWatch was configured to collect EC2 system metrics using the custom namespace:

`ComplaintSystem/EC2`

Metrics verified during implementation include:

- `disk_used_percent`
- `mem_used_percent`
- `TestMetric`

### AWS CloudTrail

CloudTrail was configured to record AWS API activity.

Trail:

`complaint-system-cloudtrail`

Logging was successfully verified with:

`IsLogging: true`

CloudTrail was also used to verify AWS activity related to services such as SQS.

### Application Load Balancer

An Application Load Balancer was configured as the public entry point for the application.

It distributes incoming traffic to the EC2 application infrastructure.

### Auto Scaling

Auto Scaling was configured to provide scalable EC2 capacity.

This allows the infrastructure to respond to changing application demand and improves availability.

### Amazon VPC

The AWS infrastructure runs inside an Amazon VPC using networking components including:

- Subnets
- Route tables
- Security groups
- Internet connectivity

## Docker

The PHP application is containerized using Docker.

The Docker image includes:

- PHP 8.3
- Apache
- MySQL/PDO extensions
- ZIP extension
- Composer

Composer dependencies are installed during the Docker image build.

Docker Compose was also used to manage the application environment.

## Jenkins CI/CD

Jenkins was used to automate the application deployment workflow.

The overall workflow is:

GitHub
↓
Jenkins
↓
Docker Build
↓
Docker Deployment
↓
AWS Infrastructure

A Jenkins pipeline was created using a `Jenkinsfile`.

## Git and GitHub

Git was used for version control and GitHub was used as the remote repository.

AWS and DevOps changes were organized in the branch:

`aws-cloud-devops`

The branch was pushed successfully to GitHub.

Latest AWS/DevOps commit:

`Add AWS cloud DevOps integration`

## Verification

### S3

Complaint files were successfully uploaded and verified using AWS CLI.

Example:

`aws s3 ls s3://<bucket>/complaints/ --recursive`

### SQS

Messages were successfully verified in the complaint event queue.

Example result:

`ApproximateNumberOfMessages: 2`

### CloudWatch

The following metrics were verified:

- `disk_used_percent`
- `mem_used_percent`
- `TestMetric`

Namespace:

`ComplaintSystem/EC2`

### CloudTrail

CloudTrail logging was verified successfully.

Example:

`IsLogging: true`

### Docker

The application container was verified as running.

Container:

`complaint-app`

The application was exposed through Docker port mapping.

## Problems Faced and Solutions

### 1. Git Dubious Ownership

While working inside the Jenkins workspace, Git reported:

`fatal: detected dubious ownership in repository`

The problem occurred because the Jenkins workspace was owned by a different user.

The repository was added as a Git safe directory:

`git config --global --add safe.directory /var/lib/jenkins/workspace/complaint-ticket-management`

### 2. Git Branch Permission Error

When creating the DevOps branch, Git initially returned:

`Unable to create ... aws-cloud-devops.lock: Permission denied`

The `.git` directory had ownership issues.

The problem was resolved by changing ownership of the Git metadata:

`sudo chown -R ubuntu:ubuntu .git`

The branch was then created successfully:

`git switch -c aws-cloud-devops`

### 3. Linux File Permission Problems

Temporary backup files created during testing could not initially be removed because of file ownership and permission issues.

The files were safely removed using elevated permissions:

`sudo rm -f <backup-file>`

The repository was then verified with:

`git status`

Result:

`working tree clean`

### 4. IAM Permission Issues

During AWS configuration, some AWS CLI operations initially returned authorization errors.

The required permissions were added to the EC2 IAM role where necessary.

This allowed the EC2 instance to interact with the required AWS services without embedding AWS access keys in the application.

### 5. CloudWatch Permission Issue

CloudWatch metric publishing initially required additional IAM permissions.

After updating the EC2 role permissions, CloudWatch metrics were successfully published and verified.

### 6. Docker and Composer Integration

The original Docker image required additional PHP dependencies for AWS SDK and Composer-based dependencies.

The Dockerfile was updated to install the ZIP extension and Composer and to run:

`composer install --no-dev --optimize-autoloader`

This allowed the PHP application to use the required AWS SDK dependencies inside the container.

## Key Learning

This project provided hands-on experience with:

- AWS cloud infrastructure
- EC2 deployment
- RDS database architecture
- S3 object storage
- SQS messaging
- CloudWatch monitoring
- CloudTrail auditing
- IAM permissions
- VPC networking
- Application Load Balancer
- Auto Scaling
- Docker
- Docker Compose
- Jenkins CI/CD
- Git and GitHub
- Linux permissions
- Troubleshooting AWS infrastructure

The project also provided practical experience in diagnosing and resolving real deployment problems rather than only configuring services individually.

## Conclusion

This project demonstrates the transformation of a PHP/MySQL complaint management application into a cloud-based DevOps deployment.

The final implementation combines application development with AWS infrastructure, containerization, CI/CD, monitoring, auditing, storage, messaging, load balancing, and scalability.

The main focus was not only deploying the application, but also troubleshooting real infrastructure problems involving IAM permissions, Git ownership, Linux file permissions, Docker, Jenkins, and AWS service integration.
