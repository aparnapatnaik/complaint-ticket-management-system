# Complaint Ticket Management System

A PHP and MySQL based Complaint Ticket Management System deployed using Docker on AWS and automated using Jenkins CI/CD.

This project was extended from a basic web application into a cloud-based DevOps deployment using AWS services for storage, messaging, monitoring, auditing, networking, load balancing and scalability.

## Project Overview

The application allows users to submit complaints and administrators to manage complaint tickets.

The original application was built using:

- PHP
- MySQL
- Bootstrap
- Apache

During the project, I containerized the application using Docker and deployed it on AWS. I also integrated multiple AWS services and created a CI/CD workflow using Jenkins and GitHub.

## Architecture

User
  |
  v
Application Load Balancer
  |
  v
EC2 / Auto Scaling
  |
  v
Docker Container
  |
  v
PHP + Apache Application
  |
  v
Amazon RDS MySQL

Supporting AWS Services:

- Amazon S3 - file and attachment storage
- Amazon SQS - asynchronous complaint events
- Amazon CloudWatch - monitoring and metrics
- AWS CloudTrail - API auditing
- Amazon VPC - networking
- IAM - permissions and roles
- Application Load Balancer - traffic distribution
- Auto Scaling - scalability

DevOps Tools:

- Git
- GitHub
- Docker
- Docker Compose
- Jenkins

## Technologies Used

### Application

- PHP 8.3
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

- Git
- GitHub
- Docker
- Docker Compose
- Jenkins

## Amazon EC2

The application was deployed on an Ubuntu EC2 instance.

The EC2 instance acts as the application host and runs the Docker-based PHP application.

The application container is exposed through Docker and runs the complaint management application.

## Amazon RDS

Amazon RDS was used as the managed MySQL database layer.

Instead of depending only on a MySQL database running inside the application environment, the application was configured to communicate with the managed RDS database.

This separates the application layer from the database layer.

## Amazon S3

Amazon S3 was implemented for storing complaint-related files and attachments.

Complaint files are stored using an organized object structure:

complaints/<complaint-id>/<file>

S3 was tested directly from the EC2 instance.

Example verification:

aws s3 ls s3://<bucket>/complaints/ --recursive

Uploaded complaint files were successfully visible in the S3 bucket.

## Amazon SQS

Amazon SQS was implemented for complaint event messaging.

When a complaint is submitted, the application sends a message to the SQS queue.

Queue:

complaint-events

The application code was updated to use the AWS SDK and SQS client.

SQS messages were successfully generated and verified in the queue.

This demonstrates asynchronous communication between the application and a messaging service.

## Amazon CloudWatch

Amazon CloudWatch was configured for monitoring the EC2 environment.

The custom namespace used by the project is:

ComplaintSystem/EC2

Metrics verified:

- disk_used_percent
- mem_used_percent
- TestMetric

The metrics were successfully listed using the AWS CLI.

CloudWatch helped provide visibility into system resource usage.

## AWS CloudTrail

AWS CloudTrail was configured to record AWS API activity.

Trail name:

complaint-system-cloudtrail

CloudTrail logging was verified successfully.

The verification returned:

IsLogging: true

CloudTrail also provided a way to inspect AWS API activity related to the project.

## Application Load Balancer

An Application Load Balancer was configured in front of the application.

The ALB provides a stable entry point for users and distributes traffic to the application infrastructure.

This creates a more production-like architecture compared with exposing a single EC2 instance directly.

## Auto Scaling

Auto Scaling was configured for the EC2 application infrastructure.

The purpose of Auto Scaling is to automatically adjust application capacity according to the configured scaling requirements.

This improves availability and provides a foundation for handling increased traffic.

## Amazon VPC

The AWS infrastructure runs inside an Amazon VPC.

The networking setup includes components such as:

- VPC
- Subnets
- Route tables
- Security groups
- Internet connectivity

Security groups were used to control access between the required components.

## IAM

IAM was important throughout the AWS implementation.

The EC2 instance uses an IAM role:

ComplaintSystemEC2Role

The role provides the permissions required by the application and AWS CLI without requiring AWS access keys to be stored directly inside the application.

## Docker

The PHP application was containerized using Docker.

The Dockerfile was updated to support the AWS SDK dependencies.

The image uses:

PHP 8.3
Apache

Required PHP extensions include:

- mysqli
- PDO
- PDO MySQL
- ZIP

Composer was also added to the Docker image.

The Dockerfile installs Composer dependencies during the image build:

composer install --no-dev --optimize-autoloader

Docker Compose was used to manage the application environment.

The running application container was verified using:

docker ps

The application container name is:

complaint-app

## Jenkins CI/CD

Jenkins was used to automate the application deployment process.

The general workflow is:

GitHub
  |
  v
Jenkins
  |
  v
Docker Build
  |
  v
Docker Deployment
  |
  v
AWS EC2

The Jenkins workspace contains the project source code and is used during the deployment process.

## Git and GitHub

Git was used for version control and GitHub was used as the remote repository.

Repository:

https://github.com/aparnapatnaik/complaint-ticket-management-system

A dedicated branch was created for the AWS and DevOps implementation:

aws-cloud-devops

The AWS/DevOps changes were committed with:

Add AWS cloud DevOps integration

The branch was successfully pushed to GitHub.

## Problems Faced and How I Solved Them

### 1. Git Dubious Ownership

When I initially tried to use Git inside the Jenkins workspace, Git returned:

fatal: detected dubious ownership in repository

The reason was that the repository was inside the Jenkins workspace and ownership did not match the ubuntu user.

I solved this by adding the Jenkins workspace as a Git safe directory:

git config --global --add safe.directory /var/lib/jenkins/workspace/complaint-ticket-management

### 2. Git Branch Permission Error

When creating the branch, Git returned:

Unable to create refs/heads/aws-cloud-devops.lock

Permission denied

The .git directory was owned by another user.

I fixed the Git directory ownership using:

sudo chown -R ubuntu:ubuntu .git

After that, I successfully created the branch:

git switch -c aws-cloud-devops

### 3. IAM Permission Problems

During AWS configuration, some AWS CLI commands initially returned permission errors.

For example, CloudWatch metric operations required the correct IAM permissions.

I identified the missing permissions and updated the EC2 IAM role.

After updating the permissions, CloudWatch operations worked successfully.

CloudTrail permissions were also reviewed when authorization errors occurred.

This helped me understand how IAM permissions affect AWS services and how to troubleshoot AccessDenied errors.

### 4. Docker Dependency Problem

The application required AWS SDK dependencies.

The original Dockerfile only installed the basic PHP database extensions.

I updated the Dockerfile to install:

- ZIP extension
- Composer
- Composer dependencies

The Docker image could then install the required PHP packages during the build process.

### 5. Jenkins Workspace and File Permissions

Some backup files created during troubleshooting were owned by another user.

When I attempted to delete them using the ubuntu user, Linux returned:

Permission denied

I used sudo to remove the unnecessary backup files.

After cleanup, Git showed:

nothing to commit, working tree clean

This helped me understand the difference between application permissions, Linux file ownership and Git repository permissions.

## Verification

### S3 Verification

S3 objects were successfully verified using:

aws s3 ls s3://<bucket>/complaints/ --recursive

Complaint files were present in the bucket.

### SQS Verification

SQS queue messages were successfully verified.

The queue contained messages generated by the complaint application.

### CloudWatch Verification

The following metrics were verified:

- disk_used_percent
- mem_used_percent
- TestMetric

### CloudTrail Verification

CloudTrail status was successfully verified.

The trail returned:

IsLogging: true

The latest delivery time was also reported successfully.

### Docker Verification

The application container was verified using:

docker ps

The running container:

complaint-app

was mapped to the application port.

### Git Verification

The final Git branch:

aws-cloud-devops

was successfully pushed to GitHub.

The latest commit:

4a3d3cd Add AWS cloud DevOps integration

The local branch was confirmed to be synchronized with:

origin/aws-cloud-devops

## What I Learned

Through this project I learned how different parts of a real cloud deployment work together.

### AWS

I learned how to work with:

- EC2
- RDS
- S3
- SQS
- CloudWatch
- CloudTrail
- IAM
- VPC
- Application Load Balancer
- Auto Scaling

### Docker

I learned how to:

- Create Docker images
- Modify a Dockerfile
- Install PHP extensions
- Install Composer inside a container
- Install application dependencies
- Run containers
- Troubleshoot container deployment

### Jenkins

I learned how Jenkins interacts with:

- GitHub
- Git
- Docker
- EC2

I also learned that Jenkins workspaces can create Linux ownership and permission issues.

### Git

I learned:

- Git branches
- Git commits
- Git remotes
- Git push
- Detached HEAD situations
- Git safe directories
- Repository ownership problems
- Working tree cleanup

### Linux

I gained practical experience with:

- Linux file permissions
- File ownership
- sudo
- chown
- Workspace directories
- Process and container verification

### Troubleshooting

One of the most important things I learned was troubleshooting.

Instead of only following commands, I had to identify why commands failed and then solve the underlying problem.

Examples included:

- IAM AccessDenied errors
- Git dubious ownership
- Git branch creation permission errors
- Linux file permission errors
- Docker dependency issues
- AWS service integration issues

## Final Result

The original PHP/MySQL application was transformed into a cloud-based DevOps deployment.

The final solution combines:

PHP
+
MySQL / RDS
+
Docker
+
Jenkins
+
GitHub
+
EC2
+
S3
+
SQS
+
CloudWatch
+
CloudTrail
+
IAM
+
VPC
+
Application Load Balancer
+
Auto Scaling

The project demonstrates not only deployment but also practical troubleshooting of cloud infrastructure, permissions, containers, CI/CD and AWS service integrations.

## Future Improvements

Possible future improvements include:

- Implementing HTTPS using an SSL/TLS certificate
- Adding automated testing to the Jenkins pipeline
- Adding Docker image versioning
- Adding more CloudWatch alarms
- Adding centralized application logging
- Implementing stronger CI/CD deployment strategies
- Adding infrastructure as code using Terraform
- Improving security and secret management
