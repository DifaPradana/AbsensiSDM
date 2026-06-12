<link rel="stylesheet" href="{{ asset('assets/css/styles.min.css') }}" />

<style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    .wrap {
        padding: 1rem 0;
        max-width: 520px;
        margin: 0 auto;
    }

    .card {
        background: var(--color-background-primary);
        border: 0.5px solid var(--color-border-tertiary);
        border-radius: var(--border-radius-lg);
        overflow: hidden;
        margin-bottom: 1rem;
    }

    .card-header {
        padding: 1rem 1.25rem;
        border-bottom: 0.5px solid var(--color-border-tertiary);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .card-header h2 {
        font-size: 16px;
        font-weight: 500;
        color: var(--color-text-primary);
    }

    .card-body {
        padding: 1.25rem;
    }

    .left-sidebar {
        overflow-x: hidden !important;
    }

    .left-sidebar * {
        max-width: 100%;
        box-sizing: border-box;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
        padding: 3px 10px;
        border-radius: 99px;
        font-weight: 500;
    }

    .badge-warning {
        background: #FAEEDA;
        color: #854F0B;
    }

    .badge-success {
        background: #EAF3DE;
        color: #3B6D11;
    }

    .badge-info {
        background: #E6F1FB;
        color: #185FA5;
    }

    .dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
        display: inline-block;
    }

    #video-container {
        position: relative;
        width: 100%;
        height: 70vh;
        aspect-ratio: 3/4;
        background: #1a1a1a;
        border-radius: var(--border-radius-md);
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: none;
        transform: scaleX(-1);
    }

    #canvas {
        display: none;
    }

    #camera-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        color: #888;
    }

    #camera-placeholder i {
        font-size: 48px;
    }

    #preview-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: none;
        border-radius: var(--border-radius-md);
    }

    #capture-btn-overlay {
        position: absolute;
        bottom: 16px;
        left: 50%;
        transform: translateX(-50%);
        display: none;
    }

    .shutter {
        width: 58px;
        height: 58px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.9);
        border: 3px solid white;
        cursor: pointer;
        transition: transform 0.1s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .shutter:active {
        transform: scale(0.92);
    }

    .shutter-inner {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: white;
    }

    #retake-bar {
        display: none;
        justify-content: space-between;
        align-items: center;
        margin-top: 8px;
    }

    .btn:disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }

    .section-label {
        font-size: 12px;
        font-weight: 500;
        color: var(--color-text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 8px;
    }

    .gps-row {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 12px;
        background: var(--color-background-secondary);
        border-radius: var(--border-radius-md);
        border: 0.5px solid var(--color-border-tertiary);
    }

    .gps-text {
        flex: 1;
    }

    .gps-text p {
        font-size: 13px;
        color: var(--color-text-primary);
    }

    .gps-text span {
        font-size: 11px;
        color: var(--color-text-secondary);
    }

    textarea {
        width: 100%;
        min-height: 90px;
        padding: 10px 12px;
        border: 0.5px solid var(--color-border-tertiary);
        border-radius: var(--border-radius-md);
        font-size: 14px;
        color: var(--color-text-primary);
        background: var(--color-background-primary);
        resize: vertical;
    }

    textarea:focus {
        outline: none;
        border-color: #378ADD;
        box-shadow: 0 0 0 2px rgba(55, 138, 221, 0.15);
    }

    .divider {
        height: 0.5px;
        background: var(--color-border-tertiary);
        margin: 1rem 0;
    }

    .status-row {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 12px;
    }

    #submit-btn {
        width: 100%;
        padding: 11px;
        font-size: 14px;
        border-radius: var(--border-radius-md);
    }

    .success-msg {
        display: none;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        padding: 2rem 1rem;
        text-align: center;
    }

    .success-icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: #EAF3DE;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .profile-img {
        width: 110px;
        height: 110px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #e5e5e5;
    }

    /* default hidden */
    .profile-hover-text {
        position: absolute;
        inset: 0;
        border-radius: 50%;
        background: rgba(0, 0, 0, 0.45);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 13px;
        opacity: 0;
        transition: .2s;
    }

    /* desktop hover */
    .profile-hover-parent:hover .profile-hover-text {
        opacity: 1;
    }

    .avatar-sm {
        width: 35px;
        height: 35px;
        object-fit: cover;
        object-position: center;
    }

    /* MOBILE FIX */
    @media (max-width: 768px) {
        .profile-hover-text {
            opacity: 1;
            /* selalu terlihat di HP */
            font-size: 12px;
            background: rgba(0, 0, 0, 0.25);
            /* lebih soft */
        }
    }

    .abs-grid {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .abs-card {
        background: #fff;
        border: 0.5px solid rgba(118, 114, 114, 0.12);
        border-radius: 12px;
        padding: 14px 16px;
        transition: border-color 0.15s;
    }

    /* .abs-card:hover {
        border-color: rgba(0, 0, 0, 0.25);
    } */

    .abs-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }

    .abs-date {
        font-size: 14px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .abs-date i {
        font-size: 16px;
        color: #888;
    }

    .abs-body {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .abs-section {
        background: #ffffff;
        border-radius: 8px;
        border: 0.5px solid rgba(118, 114, 114, 0.12);
        padding: 10px 12px;
    }

    .abs-section-title {
        font-size: 11px;
        font-weight: 500;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .abs-row {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        margin-bottom: 6px;
        font-size: 13px;
    }

    .abs-label {
        color: #888;
        min-width: 52px;
        font-size: 12px;
        flex-shrink: 0;
    }

    .abs-val {
        color: #1a1a1a;
    }

    .abs-note {
        font-style: italic;
        color: #888;
    }

    .abs-muted {
        color: #aaa;
    }

    .abs-link {
        color: #185FA5;
        text-decoration: none;
    }

    .abs-link:hover {
        text-decoration: underline;
    }

    .abs-badge {
        font-size: 11px;
        font-weight: 500;
        padding: 3px 9px;
        border-radius: 20px;
        display: inline-block;
    }

    .abs-badge-hadir {
        background: #EAF3DE;
        color: #3B6D11;
    }

    .abs-badge-izin {
        background: #FAEEDA;
        color: #854F0B;
    }

    .abs-badge-sakit {
        background: #E6F1FB;
        color: #185FA5;
    }

    .abs-badge-alfa {
        background: #FCEBEB;
        color: #A32D2D;
    }

    .abs-badge-default {
        background: #f0f0ee;
        color: #555;
    }

    .abs-badge-terlambat {
        background: #FCEBEB;
        color: #A32D2D;
    }

    .abs-badge-tepat {
        background: #EAF3DE;
        color: #3B6D11;
    }

    .abs-badge-cepat {
        background: #FAEEDA;
        color: #854F0B;
    }

    .abs-photo-btn {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 12px;
        padding: 3px 10px;
        border-radius: 8px;
        border: 0.5px solid rgba(0, 0, 0, 0.2);
        background: #fff;
        color: #555;
        cursor: pointer;
        transition: background 0.12s;
    }

    .abs-photo-btn:hover {
        background: #ebebeb;
    }

    .abs-empty {
        text-align: center;
        padding: 2rem;
        color: #aaa;
        font-size: 14px;
    }

    @media (max-width: 576px) {
        .abs-body {
            grid-template-columns: 1fr;
        }

        .abs-summary-bar {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .stat-card {
        transition: opacity 0.15s, transform 0.15s;
    }

    .stat-card:hover {
        opacity: 0.85;
        transform: translateY(-1px);
    }
</style>