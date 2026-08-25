<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        ComplaintSys - Complaint Management System
    </title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

    <style>

        body {
            margin: 0;
            background: #f8fafc;
        }

        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
        }

        .hero-container {
            max-width: 1000px;
            width: 100%;
            text-align: center;
        }

        .hero-logo {
            font-size: 32px;
            font-weight: bold;
            color: #172033;
            margin-bottom: 40px;
        }

        .hero-logo span {
            color: #2563eb;
        }

        .hero h1 {
            font-size: 48px;
            margin-bottom: 20px;
            color: #172033;
        }

        .hero p {
            max-width: 650px;
            margin: 0 auto 35px;
            color: #64748b;
            font-size: 18px;
            line-height: 1.7;
        }

        .hero-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .hero-buttons a {
            text-decoration: none;
            padding: 13px 25px;
            border-radius: 8px;
            font-weight: 600;
        }

        .btn-main {
            background: #2563eb;
            color: white;
        }

        .btn-outline {
            border: 1px solid #cbd5e1;
            color: #172033;
            background: white;
        }

        .features {
            margin-top: 60px;
            display: grid;
            grid-template-columns:
                repeat(3, 1fr);
            gap: 20px;
        }

        .feature {
            background: white;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .feature h3 {
            margin-bottom: 10px;
        }

        .feature p {
            font-size: 14px;
            margin: 0;
        }

        @media (max-width: 700px) {

            .hero h1 {
                font-size: 34px;
            }

            .features {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>

<section class="hero">

    <div class="hero-container">

        <div class="hero-logo">

            Complaint<span>Sys</span>

        </div>


        <h1>
            Complaint Management System
        </h1>


        <p>

            Submit complaints, track their progress,
            communicate with administrators and
            stay updated until your issue is resolved.

        </p>


        <div class="hero-buttons">

            <a
                href="user/login.php"
                class="btn-main"
            >
                User Login
            </a>

            <a
                href="user/register.php"
                class="btn-outline"
            >
                Create Account
            </a>

            <a
                href="admin/login.php"
                class="btn-outline"
            >
                Admin Login
            </a>

        </div>


        <div class="features">

            <div class="feature">

                <h3>
                    Submit Complaints
                </h3>

                <p>
                    Easily submit and describe
                    your problems online.
                </p>

            </div>


            <div class="feature">

                <h3>
                    Track Progress
                </h3>

                <p>
                    Monitor your complaint status
                    in real time.
                </p>

            </div>


            <div class="feature">

                <h3>
                    Get Responses
                </h3>

                <p>
                    Communicate with administrators
                    through complaint responses.
                </p>

            </div>

        </div>

    </div>

</section>

</body>

</html>
