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
                sh 'docker build -t complaint-system:latest .'
            }
        }

        stage('Docker Test') {
            steps {
                sh 'docker images | grep complaint-system || true'
            }
        }
    }
}
