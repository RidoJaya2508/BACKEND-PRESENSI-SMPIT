<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presensi SMPIT - Service Running</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 60px 40px;
            text-align: center;
            max-width: 800px;
            width: 100%;
        }

        .status-header {
            margin-bottom: 40px;
        }

        .status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 30px;
        }

        .status-dot {
            width: 16px;
            height: 16px;
            background-color: #10b981;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            }
            50% {
                box-shadow: 0 0 0 10px rgba(16, 185, 129, 0);
            }
        }

        .status-text {
            font-size: 18px;
            font-weight: 600;
            color: #10b981;
        }

        h1 {
            font-size: 48px;
            color: #1f2937;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .subtitle {
            font-size: 18px;
            color: #6b7280;
            margin-bottom: 50px;
            font-weight: 300;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
            text-align: left;
        }

        .info-card {
            background: #f9fafb;
            padding: 25px;
            border-radius: 12px;
            border-left: 4px solid #667eea;
        }

        .info-label {
            font-size: 12px;
            font-weight: 700;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .info-value {
            font-size: 16px;
            color: #1f2937;
            font-weight: 600;
            word-break: break-all;
            font-family: 'Courier New', monospace;
        }

        .endpoints {
            background: #f3f4f6;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 40px;
            text-align: left;
        }

        .endpoints h3 {
            font-size: 16px;
            color: #374151;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .endpoint-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        .endpoint-item {
            background: white;
            padding: 12px 15px;
            border-radius: 8px;
            font-size: 13px;
            font-family: 'Courier New', monospace;
            color: #1f2937;
            border-left: 3px solid #667eea;
        }

        .endpoint-method {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            margin-right: 8px;
        }

        .buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 14px 30px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #e5e7eb;
            color: #374151;
        }

        .btn-secondary:hover {
            background: #d1d5db;
            transform: translateY(-2px);
        }

        .documentation {
            margin-top: 40px;
            padding: 20px;
            background: #eff6ff;
            border-radius: 10px;
            border-left: 4px solid #3b82f6;
        }

        .documentation p {
            color: #1f2937;
            font-size: 14px;
            margin: 10px 0;
        }

        .documentation a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .documentation a:hover {
            text-decoration: underline;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin: 40px 0;
            text-align: center;
        }

        .feature-box {
            padding: 20px;
            border-radius: 10px;
            background: #f9fafb;
        }

        .feature-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .feature-title {
            font-weight: 600;
            color: #374151;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            h1 {
                font-size: 36px;
            }

            .container {
                padding: 40px 25px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="status-header">
            <div class="status-indicator">
                <div class="status-dot"></div>
                <span class="status-text">RUNNING - ONLINE</span>
            </div>
            <h1>🎓 Presensi SMPIT</h1>
            <p class="subtitle">Server Attendance Management System</p>
        </div>

        <div class="features">
            <div class="feature-box">
                <div class="feature-icon">📊</div>
                <div class="feature-title">Attendance Tracking</div>
            </div>
            <div class="feature-box">
                <div class="feature-icon">👥</div>
                <div class="feature-title">Student Management</div>
            </div>
            <div class="feature-box">
                <div class="feature-icon">📅</div>
                <div class="feature-title">Schedule Management</div>
            </div>
            <div class="feature-box">
                <div class="feature-icon">🔔</div>
                <div class="feature-title">Telegram Integration</div>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-card">
                <div class="info-label">Server Address</div>
                <div class="info-value">http://localhost:8000</div>
            </div>
            <div class="info-card">
                <div class="info-label">Database</div>
                <div class="info-value">MySQL - presensi_smpit</div>
            </div>
            <div class="info-card">
                <div class="info-label">Environment</div>
                <div class="info-value">{{ config('app.env') }}</div>
            </div>
            <div class="info-card">
                <div class="info-label">Laravel Version</div>
                <div class="info-value">{{ app()->version() }}</div>
            </div>
        </div>

        <div class="endpoints">
            <h3>📡 API Endpoints</h3>
            <div class="endpoint-list">
                <div class="endpoint-item">
                    <span class="endpoint-method">GET/POST</span>/api/students
                </div>
                <div class="endpoint-item">
                    <span class="endpoint-method">GET/POST</span>/api/classes
                </div>
                <div class="endpoint-item">
                    <span class="endpoint-method">GET/POST</span>/api/subjects
                </div>
                <div class="endpoint-item">
                    <span class="endpoint-method">GET/POST</span>/api/attendances
                </div>
                <div class="endpoint-item">
                    <span class="endpoint-method">GET/POST</span>/api/schedules
                </div>
                <div class="endpoint-item">
                    <span class="endpoint-method">POST</span>/api/auth/login
                </div>
            </div>
        </div>

        <div class="buttons">
            <a href="/api/students" class="btn btn-primary">📊 View Students API</a>
            <a href="http://localhost:3000" class="btn btn-secondary">🌐 Frontend</a>
        </div>

        <div class="documentation">
            <p>🚀 <strong>Server is running and ready for API requests</strong></p>
            <p>For comprehensive API documentation and integration guides, please refer to the backend setup documentation.</p>
            <p>The frontend application is configured to connect to this server at <code>http://localhost:8000/api</code></p>
        </div>
    </div>
</body>
</html>
