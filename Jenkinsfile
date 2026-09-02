pipeline {
    agent any

    stages {

        stage('Checkout') {
            steps {
                echo 'Checking out Complaint Management System'
                checkout scm
            }
        }

        stage('Docker Build') {
            steps {
                sh 'docker build -t complaint-ticket-management-app:latest .'
            }
        }

        stage('Docker Test') {
            steps {
                sh 'docker images | grep complaint-ticket-management-app'
            }
        }

        stage('Deploy') {
            steps {
                sh '''
                    docker rm -f complaint-app || true
                    docker compose up -d
                    sleep 10
                    docker ps
                    curl -f http://localhost:8081
                '''
            }
        }
    }

    post {
        success {
            echo 'CI/CD Pipeline completed successfully!'
        }

        failure {
            echo 'CI/CD Pipeline failed!'
        }
    }
}
