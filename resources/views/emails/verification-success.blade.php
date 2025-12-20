<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verified - {{ config('app.name') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #0077BE 0%, #00B4A6 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
            padding: 48px 32px;
            text-align: center;
            animation: slideUp 0.5s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .icon-container {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            border-radius: 50%;
            margin: 0 auto 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: scaleIn 0.6s ease-out 0.2s both;
        }
        
        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }
        
        .icon {
            width: 40px;
            height: 40px;
            stroke: white;
            stroke-width: 3;
            stroke-linecap: round;
            stroke-linejoin: round;
            fill: none;
        }
        
        .checkmark {
            stroke-dasharray: 100;
            stroke-dashoffset: 100;
            animation: drawCheck 0.6s ease-out 0.4s forwards;
        }
        
        @keyframes drawCheck {
            to {
                stroke-dashoffset: 0;
            }
        }
        
        h1 {
            font-size: 28px;
            font-weight: 700;
            color: #1A202C;
            margin-bottom: 12px;
            animation: fadeIn 0.6s ease-out 0.3s both;
        }
        
        .user-name {
            font-size: 20px;
            color: #0077BE;
            margin-bottom: 16px;
            animation: fadeIn 0.6s ease-out 0.4s both;
        }
        
        p {
            font-size: 16px;
            color: #718096;
            line-height: 1.6;
            margin-bottom: 32px;
            animation: fadeIn 0.6s ease-out 0.5s both;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        .info-box {
            background: #F8FAFB;
            border-left: 4px solid #0077BE;
            padding: 16px;
            border-radius: 8px;
            text-align: left;
            margin-top: 24px;
            animation: fadeIn 0.6s ease-out 0.6s both;
        }
        
        .info-box strong {
            color: #1A202C;
            font-size: 14px;
            display: block;
            margin-bottom: 8px;
        }
        
        .info-box p {
            font-size: 14px;
            color: #718096;
            margin: 0;
        }
        
        .close-hint {
            font-size: 14px;
            color: #9CA3AF;
            margin-top: 24px;
            animation: fadeIn 0.6s ease-out 0.7s both;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon-container">
            <svg class="icon" viewBox="0 0 52 52">
                <circle class="checkmark" cx="26" cy="26" r="25"/>
                <path class="checkmark" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
            </svg>
        </div>
        
        <h1>Email Verified!</h1>
        
        @if(isset($userName))
        <div class="user-name">Welcome, {{ $userName }}!</div>
        @endif
        
        <p>{{ $message }}</p>
        
        <div class="info-box">
            <strong>📱 Next Steps:</strong>
            <p>Return to the app on your device. The app will automatically detect your verified email and redirect you to the home page.</p>
        </div>
        
        <p class="close-hint">You can safely close this window</p>
    </div>
</body>
</html>
