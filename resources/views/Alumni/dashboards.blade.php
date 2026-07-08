@php
    use App\Models\Alumni;
    $alumni = Alumni::where('user_id', session('id'))->first();
    // alumni columns (fullname,id_no,phone,email,program,year(year of graduation),gender)
    // Get the current academic session
    // session table columns: title, status (1 = active, 0 = inactive)
    $currentSession = DB::table('session')->where('status', 1)->first();
@endphp

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    .alumni-dashboard-wrapper {
        background: #f5f7fa;
        min-height: 100vh;
        padding: 15px;
    }

    /* Header Section */
    .dashboard-header {
        background: linear-gradient(135deg, #3fa1e5 0%, #1e88e5 100%);
        border-radius: 16px;
        padding: 30px 20px;
        margin-bottom: 20px;
        color: white;
        box-shadow: 0 4px 20px rgba(63, 161, 229, 0.25);
    }

    .dashboard-header h1 {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .dashboard-header p {
        font-size: 14px;
        opacity: 0.9;
        margin: 0;
    }

    /* Info Grid */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 12px;
        margin-bottom: 20px;
    }

    .info-item {
        background: white;
        border-radius: 12px;
        padding: 18px 15px;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        transition: all 0.3s ease;
    }

    .info-item:hover {
        transform: translateY(-4px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
    }

    .info-item-icon {
        width: 48px;
        height: 48px;
        margin: 0 auto 12px;
        background: linear-gradient(135deg, #3fa1e5, #1e88e5);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
    }

    .info-item-label {
        font-size: 12px;
        color: #888;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-item-value {
        font-size: 16px;
        font-weight: 700;
        color: #2c3e50;
        word-break: break-word;
    }

    /* Election Card */
    .election-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        margin-bottom: 20px;
    }

    .election-header {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        padding: 20px;
        color: white;
        position: relative;
    }

    .election-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255, 255, 255, 0.25);
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .election-header h2 {
        font-size: 20px;
        font-weight: 700;
        margin: 0;
        line-height: 1.3;
    }

    .election-body {
        padding: 20px;
    }

    .election-info-row {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 16px;
        padding-bottom: 16px;
        border-bottom: 1px solid #f0f0f0;
    }

    .election-info-row:last-of-type {
        border-bottom: none;
    }

    .election-icon {
        width: 40px;
        height: 40px;
        background: #f0f8ff;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #3fa1e5;
        font-size: 18px;
        flex-shrink: 0;
    }

    .election-info-content {
        flex: 1;
    }

    .election-info-label {
        font-size: 12px;
        color: #888;
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .election-info-value {
        font-size: 15px;
        color: #2c3e50;
        font-weight: 600;
    }

    .election-description {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 16px;
        margin: 20px 0;
        border-left: 4px solid #3fa1e5;
    }

    .election-description h3 {
        font-size: 14px;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 10px;
    }

    .election-description p {
        font-size: 14px;
        line-height: 1.6;
        color: #555;
        margin-bottom: 10px;
    }

    .election-description ul {
        list-style: none;
        padding: 0;
        margin: 12px 0 0 0;
    }

    .election-description li {
        padding: 8px 0 8px 24px;
        position: relative;
        font-size: 14px;
        color: #555;
        line-height: 1.5;
    }

    .election-description li:before {
        content: "✓";
        position: absolute;
        left: 0;
        color: #3fa1e5;
        font-weight: 700;
    }

    .btn-vote {
        display: block;
        width: 100%;
        background: linear-gradient(135deg, #3fa1e5 0%, #1e88e5 100%);
        color: white;
        text-align: center;
        padding: 16px 24px;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 700;
        font-size: 16px;
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 4px 12px rgba(63, 161, 229, 0.3);
    }

    .btn-vote:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(63, 161, 229, 0.4);
        color: white;
        text-decoration: none;
    }

    .btn-vote i {
        margin-right: 8px;
    }

    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
            transform: scale(1);
        }

        50% {
            opacity: 0.8;
            transform: scale(1.05);
        }
    }

    .pulse {
        animation: pulse 2s ease-in-out infinite;
    }

    /* Responsive Design */
    @media (min-width: 576px) {
        .alumni-dashboard-wrapper {
            padding: 20px;
        }

        .dashboard-header {
            padding: 40px 30px;
        }

        .dashboard-header h1 {
            font-size: 28px;
        }

        .dashboard-header p {
            font-size: 15px;
        }

        .info-grid {
            gap: 15px;
        }

        .election-header h2 {
            font-size: 22px;
        }

        .election-body {
            padding: 25px;
        }
    }

    @media (min-width: 768px) {
        .dashboard-header h1 {
            font-size: 32px;
        }

        .info-grid {
            grid-template-columns: repeat(4, 1fr);
        }

        .election-header {
            padding: 25px 30px;
        }

        .election-header h2 {
            font-size: 24px;
        }

        .btn-vote {
            width: auto;
            display: inline-block;
        }
    }

    @media (min-width: 992px) {
        .alumni-dashboard-wrapper {
            padding: 25px;
            max-width: 1200px;
            margin: 0 auto;
        }
    }
</style>

<div class="alumni-dashboard-wrapper">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <h1>Welcome, {{ $alumni->fullname }}! 👋</h1>
        <p>University of Maiduguri Alumni Dashboard</p>
    </div>

    <!-- Alumni Info Grid -->
    <div class="info-grid">
        <div class="info-item">
            <div class="info-item-icon">
                <i class="fas fa-id-card"></i>
            </div>
            <div class="info-item-label">ID Number</div>
            <div class="info-item-value">{{ $alumni->id_no }}</div>
        </div>

        <div class="info-item">
            <div class="info-item-icon">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="info-item-label">Course of Study</div>
            <div class="info-item-value">{{ $alumni->program }}</div>
        </div>

        <div class="info-item">
            <div class="info-item-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="info-item-label">Graduation Year</div>
            <div class="info-item-value">{{ $alumni->year }}</div>
        </div>

        <div class="info-item">
            <div class="info-item-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="info-item-label">Status</div>
            <div class="info-item-value" style="color: #27ae60;">Verified</div>
        </div>
    </div>

    <!-- Election Card -->
    <div class="election-card">
        <div class="election-header">
            <div class="election-badge pulse">
                <i class="fas fa-star"></i>
                <span>Featured Event</span>
            </div>
            <h2>Alumni Association General Election 2025</h2>
        </div>

        <div class="election-body">
            <!-- Election Info Rows -->
            <div class="election-info-row">
                <div class="election-icon">
                    <i class="fas fa-calendar"></i>
                </div>
                <div class="election-info-content">
                    <div class="election-info-label">Date</div>
                    <div class="election-info-value">Saturday, 11th October 2025</div>
                </div>
            </div>

            <div class="election-info-row">
                <div class="election-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="election-info-content">
                    <div class="election-info-label">Time</div>
                    <div class="election-info-value">08:00 AM to 01:00 PM WAT</div>
                </div>
            </div>

            <div class="election-info-row">
                <div class="election-icon">
                    <i class="fas fa-vote-yea"></i>
                </div>
                <div class="election-info-content">
                    <div class="election-info-label">Event Type</div>
                    <div class="election-info-value">Leadership Voting</div>
                </div>
            </div>

            <!-- Election Description -->
            <div class="election-description">
                <h3>Important Notice</h3>
                <p>The University of Maiduguri Alumni Association will be conducting its general election to elect new
                    leadership. All registered alumni members are encouraged to participate in this democratic process.
                </p>

                <h3 style="margin-top: 16px;">What to Expect:</h3>
                <ul>
                    <li>Electronic voting through this portal</li>
                    <li>Must be logged in to cast your vote</li>
                    <li>Your vote is confidential and secure</li>
                </ul>
            </div>

            <!-- Vote Button -->
            <a href="/election general" class="btn-vote">
                <i class="fas fa-vote-yea"></i>
                Proceed to Vote Now
            </a>
        </div>
    </div>
</div>
