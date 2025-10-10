@extends('layouts.app')

@section('title', 'SURVEI INDUSTRI BESAR DAN SEDANG TRIWULANAN (SIBSTR) - DataKita')
@section('description', 'Survei Industri Besar dan Sedang Triwulanan - Formulir Pengumpulan Data')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

    :root {
        --primary-color: #2563eb;
        --secondary-color: #1e40af;
        --accent-color: #3b82f6;
        --text-primary: #1f2937;
        --text-secondary: #4b5563;
        --bg-light: #f8fafc;
    }

    body {
        font-family: 'Poppins', sans-serif;
        background: var(--bg-light);
    }

    .survey-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem;
        background: white;
        min-height: 100vh;
    }

    .dark .survey-container {
        background: #111827;
    }

    /* Hero Section - Clean and Modern */
    .survey-hero {
        padding: 4rem 2rem;
        text-align: center;
        background: white;
        margin-bottom: 3rem;
    }

    .dark .survey-hero {
        background: #111827;
    }

    .survey-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 1rem;
        line-height: 1.2;
    }

    .dark .survey-title {
        color: #f9fafb;
    }

    .survey-subtitle {
        font-size: 1.25rem;
        color: #64748b;
        margin-bottom: 2rem;
        font-weight: 400;
    }

    .dark .survey-subtitle {
        color: #94a3b8;
    }

    /* Logo Container - Two Logo Layout */
    .logo-container {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 4rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }

    .logo-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        transition: transform 0.3s ease;
    }

    .logo-item:hover {
        transform: scale(1.05);
    }

    .bps-logo, .se2026-logo {
        width: 180px;
        height: auto;
        transition: transform 0.3s ease;
        filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.1));
    }

    /* Responsive Design for Mobile */
    @media (max-width: 768px) {
        .logo-container {
            flex-direction: column;
            gap: 2rem;
        }
        
        .bps-logo, .se2026-logo {
            width: 150px;
        }
    }

    @media (max-width: 480px) {
        .logo-container {
            gap: 1.5rem;
        }
        
        .bps-logo, .se2026-logo {
            width: 120px;
        }
    }

    /* Content Sections - Consistent Card Style */
    .content-section {
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        border: 2px solid #e2e8f0;
        border-radius: 1.5rem;
        padding: 2.5rem;
        margin: 2rem 0;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }

    .dark .content-section {
        background: linear-gradient(135deg, #374151 0%, #1f2937 100%);
        border-color: #4b5563;
    }

    .section-title {
        font-size: 1.75rem;
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        letter-spacing: -0.025em;
    }

    .dark .section-title {
        color: #f9fafb;
    }

    .section-content {
        color: var(--text-secondary);
        line-height: 1.7;
        font-size: 1rem;
    }

    /* Contact Section - Clean and Simple */
    .contact-section {
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        border: 2px solid #e2e8f0;
        border-radius: 1.5rem;
        padding: 2.5rem;
        margin: 2rem 0;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        border-left: 4px solid var(--primary-color);
    }

    .dark .contact-section {
        background: linear-gradient(135deg, #374151 0%, #1f2937 100%);
        border-color: #4b5563;
        border-left-color: #60a5fa;
    }

    .contact-title {
        font-size: 1.5rem;
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        letter-spacing: -0.025em;
    }

    .dark .contact-title {
        color: #f9fafb;
    }

    .contact-content {
        color: #64748b;
        line-height: 1.7;
        font-size: 1rem;
    }

    .dark .contact-content {
        color: #94a3b8;
    }

    .contact-content a {
        color: var(--primary-color);
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .dark .contact-content a {
        color: #60a5fa;
    }

    .contact-content a:hover {
        color: var(--secondary-color);
        text-decoration: underline;
    }

    .dark .contact-content a:hover {
        color: #3b82f6;
    }

    .contact-content strong {
        color: #1f2937;
        font-weight: 600;
    }

    .dark .contact-content strong {
        color: #f9fafb;
    }

    /* Template Section - Card Style */
    .template-section {
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        border: 2px solid #e2e8f0;
        border-radius: 1.5rem;
        padding: 2.5rem;
        margin: 2rem 0;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }

    .dark .template-section {
        background: linear-gradient(135deg, #374151 0%, #1f2937 100%);
        border-color: #4b5563;
    }

    .template-title {
        font-size: 1.5rem;
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        letter-spacing: -0.025em;
    }

    .dark .template-title {
        color: #f9fafb;
    }

    .template-description {
        color: var(--text-secondary);
        margin-bottom: 1.5rem;
        line-height: 1.7;
    }



    /* Form Section - Clean and Modern */
    .form-section {
        padding: 0;
        background: transparent;
    }

    .form-group {
        margin-bottom: 2rem;
    }

    .form-label {
        display: block;
        font-weight: 700;
        color: #374151;
        margin-bottom: 1rem;
        font-size: 1.1rem;
        letter-spacing: -0.025em;
    }

    .dark .form-label {
        color: #d1d5db;
    }



    .radio-group {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-top: 1rem;
    }

    .radio-item-card {
        display: block;
        position: relative;
        cursor: pointer;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        padding: 0;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .radio-item-card:hover {
        border-color: #3b82f6;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px -8px rgba(59, 130, 246, 0.25);
        background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%);
    }

    .radio-item-card input[type="radio"] {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }

    .radio-card-content {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.5rem;
        position: relative;
    }

    .radio-indicator {
        width: 24px;
        height: 24px;
        border: 2px solid #d1d5db;
        border-radius: 50%;
        position: relative;
        transition: all 0.3s ease;
        flex-shrink: 0;
        background: #ffffff;
    }

    .radio-dot {
        width: 12px;
        height: 12px;
        background: #3b82f6;
        border-radius: 50%;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) scale(0);
        transition: transform 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }

    .radio-text {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        flex: 1;
    }

    .radio-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1f2937;
        transition: color 0.3s ease;
    }

    .radio-subtitle {
        font-size: 0.875rem;
        color: #6b7280;
        line-height: 1.4;
        transition: color 0.3s ease;
    }

    .radio-item-card input[type="radio"]:checked ~ .radio-card-content .radio-indicator {
        border-color: #3b82f6;
        background: #ffffff;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
    }

    .radio-item-card input[type="radio"]:checked ~ .radio-card-content .radio-dot {
        transform: translate(-50%, -50%) scale(1);
    }

    .radio-item-card input[type="radio"]:checked ~ .radio-card-content .radio-title {
        color: #3b82f6;
    }

    .radio-item-card input[type="radio"]:checked ~ .radio-card-content .radio-subtitle {
        color: #4b5563;
    }

    .radio-item-card:has(input[type="radio"]:checked) {
        border-color: #3b82f6;
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        box-shadow: 0 4px 20px -4px rgba(59, 130, 246, 0.3);
    }

    .radio-item-card:has(input[type="radio"]:checked)::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(37, 99, 235, 0.02) 100%);
        pointer-events: none;
    }
        color: #1f2937;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        letter-spacing: -0.025em;
    }

    .dark .instructions-title {
        color: #f9fafb;
    }

    .instructions-content {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 2rem;
    }

    .instruction-step {
        background: white;
        padding: 2rem;
        border-radius: 1rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .instruction-step::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #3b82f6, #1d4ed8);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .instruction-step:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.15);
    }

    .instruction-step:hover::before {
        transform: scaleX(1);
    }

    .dark .instruction-step {
        background: #374151;
        border-color: #4b5563;
    }

    .step-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.5rem;
        height: 2.5rem;
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        color: white;
        border-radius: 50%;
        font-weight: 800;
        font-size: 1rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
    }

    .step-title {
        color: #1f2937;
        margin-bottom: 1rem;
        font-size: 1.25rem;
        font-weight: 700;
        letter-spacing: -0.025em;
    }

    .dark .step-title {
        color: #f9fafb;
    }

    .step-content {
        color: #4b5563;
        line-height: 1.7;
        font-size: 1.05rem;
    }

    .dark .step-content {
        color: #d1d5db;
    }

    .contact-info {
        background: #dbeafe;
        border: 1px solid #3b82f6;
        border-radius: 0.75rem;
        padding: 1.5rem;
        margin-top: 1.5rem;
    }

    .dark .contact-info {
        background: rgba(59, 130, 246, 0.1);
        border-color: #60a5fa;
    }



    .form-group {
        margin-bottom: 2.5rem;
    }



    .form-control {
        width: 100%;
        padding: 1.25rem;
        border: 2px solid #e2e8f0;
        border-radius: 0.75rem;
        font-size: 1.05rem;
        transition: all 0.3s ease-in-out;
        background: #f8fafc;
        font-weight: 500;
    }

    .form-control:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        background: white;
        transform: translateY(-2px);
    }

    .dark .form-control {
        background-color: #374151;
        border-color: #4b5563;
        color: #f9fafb;
    }

    .dark .form-control:focus {
        border-color: #60a5fa;
        box-shadow: 0 0 0 4px rgba(96, 165, 250, 0.1);
        background-color: #1f2937;
    }

    .radio-group {
        display: flex;
        gap: 1.5rem;
        margin-top: 1rem;
        flex-wrap: wrap;
    }

    .radio-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 1.5rem;
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 0.75rem;
        transition: all 0.3s ease-in-out;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .radio-item input[type="radio"] {
        width: 20px;
        height: 20px;
        margin: 0;
        cursor: pointer;
        accent-color: var(--primary-color);
        position: relative;
        z-index: 2;
    }

    .radio-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(37, 99, 235, 0.05) 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .radio-item:hover {
        border-color: #3b82f6;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px -8px rgba(59, 130, 246, 0.3);
    }

    .radio-item:hover::before {
        opacity: 1;
    }

    .radio-item input[type="radio"]:checked + label {
        color: #3b82f6;
        font-weight: 700;
    }

    .radio-item:has(input[type="radio"]:checked) {
        border-color: #3b82f6;
        background: rgba(59, 130, 246, 0.1);
    }

    .dark .radio-item-card {
        background: linear-gradient(135deg, #374151 0%, #4b5563 100%);
        border-color: #6b7280;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
    }

    .dark .radio-item-card:hover {
        border-color: #60a5fa;
        background: linear-gradient(135deg, #4b5563 0%, #374151 100%);
        box-shadow: 0 8px 25px -8px rgba(96, 165, 250, 0.25);
    }

    .dark .radio-item-card .radio-indicator {
        border-color: #9ca3af;
        background: #374151;
    }

    .dark .radio-item-card .radio-title {
        color: #f9fafb;
    }

    .dark .radio-item-card .radio-subtitle {
        color: #d1d5db;
    }

    .dark .radio-item-card input[type="radio"]:checked ~ .radio-card-content .radio-indicator {
        border-color: #60a5fa;
        background: #374151;
        box-shadow: 0 0 0 2px rgba(96, 165, 250, 0.2);
    }

    .dark .radio-item-card input[type="radio"]:checked ~ .radio-card-content .radio-dot {
        background: #60a5fa;
    }

    .dark .radio-item-card input[type="radio"]:checked ~ .radio-card-content .radio-title {
        color: #60a5fa;
    }

    .dark .radio-item-card input[type="radio"]:checked ~ .radio-card-content .radio-subtitle {
        color: #e5e7eb;
    }

    .dark .radio-item-card:has(input[type="radio"]:checked) {
        border-color: #60a5fa;
        background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
        box-shadow: 0 4px 20px -4px rgba(96, 165, 250, 0.3);
    }

    .dark .radio-item-card:has(input[type="radio"]:checked)::before {
        background: linear-gradient(135deg, rgba(96, 165, 250, 0.1) 0%, rgba(59, 130, 246, 0.05) 100%);
    }

    .dark .radio-item {
        background: #374151;
        border-color: #4b5563;
    }

    .dark .radio-item:hover {
        border-color: #60a5fa;
    }





    .template-description {
        color: #4b5563;
        margin-bottom: 2rem;
        line-height: 1.7;
        font-size: 1.05rem;
    }

    .dark .template-description {
        color: #d1d5db;
    }

    .template-links {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.5rem;
    }

    .template-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 1rem;
        padding: 1.25rem 2rem;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        text-decoration: none;
        border-radius: 1rem;
        font-weight: 700;
        transition: all 0.3s ease-in-out;
        box-shadow: 0 8px 25px -8px rgba(59, 130, 246, 0.4);
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .template-link::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
    }

    .template-link:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: white;
        text-decoration: none;
        transform: translateY(-4px);
        box-shadow: 0 20px 40px -10px rgba(59, 130, 246, 0.5);
    }

    .template-link:hover::before {
        left: 100%;
    }

    /* File Upload Area - Clean and Modern */
    .file-upload-area {
        border: 2px dashed #cbd5e1;
        border-radius: 1.5rem;
        padding: 3rem 2rem;
        text-align: center;
        transition: all 0.3s ease;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .dark .file-upload-area {
        border-color: #4b5563;
        background: linear-gradient(135deg, #374151 0%, #1f2937 100%);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .file-upload-area:hover {
        border-color: var(--primary-color);
        background: rgba(37, 99, 235, 0.05);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(37, 99, 235, 0.15);
    }

    .dark .file-upload-area:hover {
        background: rgba(59, 130, 246, 0.1);
        border-color: #60a5fa;
    }

    .file-upload-area.dragover {
        border-color: var(--primary-color);
        background: rgba(37, 99, 235, 0.1);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(37, 99, 235, 0.2);
    }

    .dark .file-upload-area.dragover {
        background: rgba(59, 130, 246, 0.15);
        border-color: #60a5fa;
    }

    .upload-icon {
        font-size: 3rem;
        color: #64748b;
        margin-bottom: 1rem;
    }

    .dark .upload-icon {
        color: #94a3b8;
    }

    .upload-text {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }

    .dark .upload-text {
        color: #f9fafb;
    }

    .upload-subtext {
        color: #64748b;
        font-size: 1rem;
    }

    .dark .upload-subtext {
        color: #94a3b8;
    }
        line-height: 1.6;
    }

    /* Button Styles - Clean and Modern */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        padding: 1rem 2rem;
        font-weight: 600;
        border-radius: 0.75rem;
        text-decoration: none;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        font-size: 1rem;
        min-width: 180px;
    }

    .btn-primary {
        background: var(--primary-color);
        color: white;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }

    .btn-primary:hover {
        background: var(--secondary-color);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(37, 99, 235, 0.4);
    }

    .dark .btn-primary {
        background: #3b82f6;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    .dark .btn-primary:hover {
        background: #2563eb;
        box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
    }

    /* Disabled Button Styles */
    .btn:disabled,
    .btn[disabled] {
        background: #9ca3af !important;
        color: #6b7280 !important;
        cursor: not-allowed !important;
        opacity: 0.6 !important;
        transform: none !important;
        box-shadow: none !important;
    }

    .dark .btn:disabled,
    .dark .btn[disabled] {
        background: #4b5563 !important;
        color: #6b7280 !important;
    }

    .btn:disabled:hover,
    .btn[disabled]:hover {
        background: #9ca3af !important;
        color: #6b7280 !important;
        transform: none !important;
        box-shadow: none !important;
    }

    .dark .btn:disabled:hover,
    .dark .btn[disabled]:hover {
        background: #4b5563 !important;
        color: #6b7280 !important;
    }

    /* Premium Submit Button - Completely New Design */
    .survey-submit-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 1rem;
        padding: 1.5rem 3rem;
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        color: white;
        text-decoration: none;
        border-radius: 1.25rem;
        font-weight: 700;
        font-size: 1.125rem;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow:
            0 10px 30px -5px rgba(5, 150, 105, 0.4),
            0 4px 15px -3px rgba(5, 150, 105, 0.2);
        text-align: center;
        position: relative;
        overflow: hidden;
        border: none;
        cursor: pointer;
        min-width: 240px;
        letter-spacing: 0.025em;
        text-transform: uppercase;
        font-family: 'Inter', sans-serif;
    }

    .survey-submit-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transition: left 0.6s ease;
    }

    .survey-submit-btn::after {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 1.25rem;
        padding: 2px;
        background: linear-gradient(135deg, #10b981, #059669, #047857);
        mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        mask-composite: xor;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .survey-submit-btn:hover {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        text-decoration: none;
        transform: translateY(-3px) scale(1.02);
        box-shadow:
            0 20px 50px -10px rgba(5, 150, 105, 0.5),
            0 8px 25px -5px rgba(5, 150, 105, 0.3);
    }

    .survey-submit-btn:hover::before {
        left: 100%;
    }

    .survey-submit-btn:hover::after {
        opacity: 1;
    }

    .survey-submit-btn:active {
        transform: translateY(-1px) scale(1.01);
        box-shadow:
            0 15px 40px -8px rgba(5, 150, 105, 0.4),
            0 6px 20px -4px rgba(5, 150, 105, 0.25);
    }

    /* Submit Button Disabled State */
    .survey-submit-btn:disabled,
    .survey-submit-btn[disabled] {
        background: linear-gradient(135deg, #9ca3af 0%, #6b7280 100%) !important;
        color: #d1d5db !important;
        cursor: not-allowed !important;
        opacity: 0.6 !important;
        transform: none !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
    }

    .survey-submit-btn:disabled::before,
    .survey-submit-btn[disabled]::before {
        display: none;
    }

    .survey-submit-btn:disabled::after,
    .survey-submit-btn[disabled]::after {
        display: none;
    }

    .survey-submit-btn:disabled:hover,
    .survey-submit-btn[disabled]:hover {
        background: linear-gradient(135deg, #9ca3af 0%, #6b7280 100%) !important;
        color: #d1d5db !important;
        transform: none !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
    }

    /* Dark Mode for Submit Button */
    .dark .survey-submit-btn {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        box-shadow:
            0 10px 30px -5px rgba(16, 185, 129, 0.4),
            0 4px 15px -3px rgba(16, 185, 129, 0.2);
    }

    .dark .survey-submit-btn:hover {
        background: linear-gradient(135deg, #34d399 0%, #10b981 100%);
        box-shadow:
            0 20px 50px -10px rgba(16, 185, 129, 0.5),
            0 8px 25px -5px rgba(16, 185, 129, 0.3);
    }

    .dark .survey-submit-btn:disabled,
    .dark .survey-submit-btn[disabled] {
        background: linear-gradient(135deg, #4b5563 0%, #374151 100%) !important;
        color: #6b7280 !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2) !important;
    }

    .dark .survey-submit-btn:disabled:hover,
    .dark .survey-submit-btn[disabled]:hover {
        background: linear-gradient(135deg, #4b5563 0%, #374151 100%) !important;
        color: #6b7280 !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2) !important;
    }

    /* Alert Styles - Clean and Simple */
    .alert {
        padding: 1.5rem;
        border-radius: 0.75rem;
        margin-bottom: 1.5rem;
        border: 1px solid;
        display: flex;
        align-items: center;
        gap: 1rem;
        font-weight: 500;
    }

    .alert-success {
        background: #f0fdf4;
        border-color: #22c55e;
        color: #15803d;
    }

    .alert-danger {
        background: #fef2f2;
        border-color: #ef4444;
        color: #dc2626;
    }

    .alert-icon {
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .error-message {
        color: #dc2626;
        font-size: 0.875rem;
        margin-top: 0.5rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-actions {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 3rem 2rem 2rem;
        border-top: 2px solid #e2e8f0;
        margin-top: 3rem;
        gap: 2rem;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 1.5rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        position: relative;
    }

    .dark .form-actions {
        border-color: #4b5563;
        background: linear-gradient(135deg, #374151 0%, #1f2937 100%);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    }

    .submit-helper-text {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #dc3545;
        font-size: 0.875rem;
        font-weight: 500;
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        border-radius: 0.375rem;
        padding: 0.75rem 1rem;
        margin-bottom: 0.5rem;
    }

    .required-note {
        text-align: center;
        color: #64748b;
        font-size: 0.875rem;
        margin-top: 1rem;
        font-style: italic;
    }

    .dark .required-note {
        color: #94a3b8;
    }

    /* Pulse animation for invalid file action buttons */
    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
    }

    /* Pulse glow animation for Add Company button */
    @keyframes pulse-glow {
        0%, 100% {
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }
        50% {
            box-shadow: 0 4px 20px rgba(59, 130, 246, 0.7), 0 0 0 4px rgba(59, 130, 246, 0.2);
        }
    }

    /* Enhanced Responsive Design */
    @media (max-width: 768px) {
        .survey-container {
            padding: 1rem;
        }

        .survey-header {
            padding: 2rem 1.5rem;
            border-radius: 1rem;
        }

        .survey-title {
            font-size: 1.75rem;
        }

        .bps-logo {
            width: 100px;
        }

        .logo-section {
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .collapsible-header {
            padding: 1.5rem;
        }

        .collapsible-title {
            font-size: 1.25rem;
        }

        .contact-section {
            padding: 2rem;
            margin: 1.5rem 0;
        }

        .form-section {
            padding: 2rem;
        }

        .instructions-content {
            grid-template-columns: 1fr;
        }

        .template-links {
            grid-template-columns: 1fr;
        }

        .radio-group {
            flex-direction: column;
            gap: 1rem;
        }

        .btn {
            width: 100%;
            min-width: auto;
        }

        .instruction-step {
            padding: 1.5rem;
        }

        .file-upload-area {
            padding: 3rem 1.5rem;
        }
    }
</style>
@endpush

@section('content')
<div class="survey-container">
    <!-- Survey Hero Section -->
    <div class="survey-hero" data-aos="fade-up">
        <div class="logo-container">
            <div class="logo-item">
                <img src="{{ asset('img/Logo BPS 1.png') }}" alt="Logo BPS" class="bps-logo">
            </div>
            <div class="logo-item">
                <img src="{{ asset('img/Logo SE2026.png') }}" alt="Logo SE2026" class="se2026-logo">
            </div>
        </div>
        <h1 class="survey-title">
            SURVEI INDUSTRI BESAR DAN SEDANG TRIWULANAN (SIBSTR)
        </h1>
        <p class="survey-subtitle">
            Formulir survei untuk pengumpulan data industri besar dan sedang triwulanan sesuai standar BPS
        </p>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success" data-aos="fade-up">
            <div class="alert-icon">✅</div>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" data-aos="fade-up">
            <div class="alert-icon">⚠️</div>
            <div>
                <strong>Terdapat kesalahan dalam pengisian:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Instructions Section -->
    <div class="content-section" data-aos="fade-up" data-aos-delay="100">
        <h2 class="section-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                <path d="M12 17h.01"></path>
            </svg>
            Tata Cara Pengisian Survei
        </h2>
        <div class="section-content">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
                <!-- Step 1: Download Template -->
                <div style="background: white; padding: 1.5rem; border-radius: 0.75rem; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);">
                    <div style="display: flex; align-items: center; justify-content: center; width: 2rem; height: 2rem; background: var(--primary-color); color: white; border-radius: 50%; font-weight: 600; margin-bottom: 1rem;">1</div>
                    <h3 style="font-size: 1.125rem; font-weight: 600; color: var(--text-primary); margin-bottom: 0.75rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; margin-right: 0.5rem; vertical-align: middle;">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7,10 12,15 17,10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        Unduh Template
                    </h3>
                    <p style="color: var(--text-secondary); line-height: 1.6; margin: 0;">Download template Excel kuesioner sesuai dengan jenis perusahaan Anda (<strong>Industri</strong> atau <strong>Non-Industri</strong>).</p>
                </div>
                
                <!-- Step 2: Fill Company Data -->
                <div style="background: white; padding: 1.5rem; border-radius: 0.75rem; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);">
                    <div style="display: flex; align-items: center; justify-content: center; width: 2rem; height: 2rem; background: var(--primary-color); color: white; border-radius: 50%; font-weight: 600; margin-bottom: 1rem;">2</div>
                    <h3 style="font-size: 1.125rem; font-weight: 600; color: var(--text-primary); margin-bottom: 0.75rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; margin-right: 0.5rem; vertical-align: middle;">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14,2 14,8 20,8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                        </svg>
                        Isi Data Perusahaan
                    </h3>
                    <p style="color: var(--text-secondary); line-height: 1.6; margin: 0;">Lengkapi informasi perusahaan pada template yang telah diunduh dan Lengkapi Formulir.</p>
                </div>
                
                <!-- Step 3: Upload File -->
                <div style="background: white; padding: 1.5rem; border-radius: 0.75rem; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);">
                    <div style="display: flex; align-items: center; justify-content: center; width: 2rem; height: 2rem; background: var(--primary-color); color: white; border-radius: 50%; font-weight: 600; margin-bottom: 1rem;">3</div>
                    <h3 style="font-size: 1.125rem; font-weight: 600; color: var(--text-primary); margin-bottom: 0.75rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; margin-right: 0.5rem; vertical-align: middle;">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14,2 14,8 20,8"></polyline>
                            <path d="M8 12h8"></path>
                            <path d="M12 8v8"></path>
                        </svg>
                        Upload File
                    </h3>
                    <p style="color: var(--text-secondary); line-height: 1.6; margin: 0;">Upload file Excel yang telah diisi lengkap melalui formulir di bawah ini untuk menyelesaikan proses survei.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Template Download Section -->
    <div class="template-section" data-aos="fade-up" data-aos-delay="200">
        <h2 class="template-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14,2 14,8 20,8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
            </svg>
            Download Template Kuesioner
        </h2>
        <p class="template-description">
            Silakan download template kuesioner sesuai dengan jenis perusahaan Anda. Template sudah disesuaikan dengan standar BPS dan berisi panduan pengisian yang lengkap.
        </p>
        <div class="template-links">
            <a href="{{ asset('kues/2025.10.03 KUESIONER SIBSTR 2025 (INDUSTRI).xlsx') }}"
               class="template-link" download>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7,10 12,15 17,10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                Template Industri
            </a>
            <a href="{{ asset('kues/2025.10.03 KUESIONER SIBSTR 2025 (NON INDUSTRI).xlsx') }}"
               class="template-link" download>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7,10 12,15 17,10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                Template Non-Industri
            </a>
        </div>
    </div>

    <!-- Survey Form Section -->
    <div class="content-section" data-aos="fade-up" data-aos-delay="300">
        <h2 class="section-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14,2 14,8 20,8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
            </svg>
            Upload Survei SIBSTR
        </h2>
        <div class="form-section">
            <form id="surveyForm" action="{{ route('temporary.survey.sibstr.submit') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Nama -->
                <div class="form-group">
                    <label for="nama" class="form-label">Nama Lengkap *</label>
                    <input type="text" id="nama" name="nama" class="form-control" value="{{ old('nama') }}" required placeholder="Masukkan nama lengkap Anda">
                    @error('nama')
                        <div class="error-message">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Jabatan -->
                <div class="form-group">
                    <label for="jabatan" class="form-label">Jabatan *</label>
                    <input type="text" id="jabatan" name="jabatan" class="form-control" value="{{ old('jabatan') }}" required placeholder="Contoh: Manajer Keuangan, Direktur, dll">
                    @error('jabatan')
                        <div class="error-message">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- No. HP -->
                <div class="form-group">
                    <label for="no_hp" class="form-label">Nomor HP *</label>
                    <input type="text" id="no_hp" name="no_hp" class="form-control" value="{{ old('no_hp') }}" required placeholder="Contoh: 08123456789">
                    @error('no_hp')
                        <div class="error-message">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email" class="form-label">Alamat Email *</label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="contoh@perusahaan.com">
                    @error('email')
                        <div class="error-message">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Perusahaan -->
                <div class="form-group">
                    <label for="company_search" class="form-label">Nama Perusahaan *</label>
                    
                    <!-- Helper text for company data workflow -->
                    <div class="company-workflow-helper" id="companyWorkflowHelper" style="background: #f0f9ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 12px; margin-bottom: 12px; font-size: 0.875rem; color: #1e40af;">
                        <div style="display: flex; align-items: flex-start; gap: 8px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-top: 2px; flex-shrink: 0;">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="m9,12 2,2 4,-4"></path>
                            </svg>
                            <div>
                                <strong>Cara mengisi data perusahaan:</strong><br>
                                1. Ketik nama perusahaan untuk mencari dari database<br>
                                2. Pilih dari daftar yang muncul, atau tambah perusahaan baru jika tidak ada<br>
                                3. Pastikan alamat perusahaan sudah terisi dengan benar
                            </div>
                        </div>
                    </div>

                    <div class="company-search-container" style="position: relative;">
                        <input type="hidden" id="company_id" name="company_id" value="{{ old('company_id') }}">
                        <input type="text" id="company_search" class="form-control"
                               placeholder="Cari nama perusahaan atau ketik untuk menambah baru..."
                               autocomplete="off"
                               style="padding-right: 40px;">
                        
                        <!-- Enhanced Add Company Button with visual cues -->
                        <button type="button" id="add_company_btn" class="add-company-btn"
                                style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: linear-gradient(135deg, #3b82f6, #1d4ed8); border: none; color: white; font-size: 14px; cursor: pointer; display: none; padding: 8px 12px; border-radius: 6px; font-weight: 600; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4); transition: all 0.3s ease; animation: pulse-glow 2s infinite;"
                                title="Klik untuk menambah perusahaan baru ke database"
                                onmouseover="this.style.transform='translateY(-50%) scale(1.05)'; this.style.boxShadow='0 6px 20px rgba(59, 130, 246, 0.6)';"
                                onmouseout="this.style.transform='translateY(-50%) scale(1)'; this.style.boxShadow='0 4px 12px rgba(59, 130, 246, 0.4)';">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 4px;">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="16"></line>
                                <line x1="8" y1="12" x2="16" y2="12"></line>
                            </svg>
                            Tambah
                        </button>
                        
                        <div id="company_dropdown" class="company-dropdown" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #d1d5db; border-top: none; border-radius: 0 0 6px 6px; max-height: 200px; overflow-y: auto; z-index: 1000; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                        </div>
                    </div>

                    <!-- Company status indicator -->
                    <div id="companyStatusIndicator" style="margin-top: 8px; font-size: 0.875rem; display: none;">
                        <div id="companySelectedStatus" style="color: #059669; display: none;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline; margin-right: 4px;">
                                <polyline points="20,6 9,17 4,12"></polyline>
                            </svg>
                            Perusahaan dipilih dari database
                        </div>
                        <div id="companyNewStatus" style="color: #d97706; display: none;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline; margin-right: 4px;">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="16"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                            Perusahaan baru - pastikan alamat sudah diisi
                        </div>
                    </div>

                    <!-- Hidden inputs for form submission -->
                    <input type="hidden" id="perusahaan" name="perusahaan" value="{{ old('perusahaan') }}" required>

                    @error('perusahaan')
                        <div class="error-message">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Alamat Perusahaan -->
                <div class="form-group">
                    <label for="alamat" class="form-label">
                        Alamat Perusahaan 
                        <span id="alamat-required-indicator" style="display: none; color: red;">*</span>
                        <span id="alamat-optional-indicator" style="color: #6b7280; font-weight: normal;">(Opsional untuk perusahaan dari database)</span>
                    </label>
                    <textarea id="alamat" name="alamat" class="form-control" rows="3"
                              placeholder="Alamat lengkap perusahaan akan terisi otomatis jika memilih dari daftar, atau isi manual jika menambah perusahaan baru">{{ old('alamat') }}</textarea>
                    
                    <!-- Address field helper text -->
                    <div id="alamatHelper" style="margin-top: 6px; font-size: 0.875rem; color: #6b7280; display: none;">
                        <div id="alamatHelperExisting" style="display: none;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline; margin-right: 4px;">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="m9,12 2,2 4,-4"></path>
                            </svg>
                            Alamat sudah tersimpan di database untuk perusahaan ini
                        </div>
                        <div id="alamatHelperNew" style="display: none; color: #d97706;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline; margin-right: 4px;">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="16"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                            Wajib diisi untuk perusahaan baru
                        </div>
                    </div>
                    @error('alamat')
                        <div class="error-message">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Jenis Perusahaan -->
                <div class="form-group">
                    <label class="form-label">Jenis Perusahaan *</label>
                    <div class="radio-group">
                        @foreach($jenisPerusahaanOptions as $value => $label)
                            <label for="jenis_{{ $value }}" class="radio-item-card">
                                <input type="radio" id="jenis_{{ $value }}" name="jenis_perusahaan" value="{{ $value }}"
                                       {{ old('jenis_perusahaan') == $value ? 'checked' : '' }} required>
                                <div class="radio-card-content">
                                    <div class="radio-indicator">
                                        <div class="radio-dot"></div>
                                    </div>
                                    <div class="radio-text">
                                        <span class="radio-title">{{ $label }}</span>
                                        <span class="radio-subtitle">
                                            @if($value === 'industri')
                                                Perusahaan manufaktur dan industri pengolahan
                                            @else
                                                Perusahaan non-manufaktur dan jasa
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('jenis_perusahaan')
                        <div class="error-message">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- File Upload -->
                <div class="form-group">
                    <label for="files" class="form-label">Upload File Kuesioner *</label>
                    <div class="file-upload-area" id="fileUploadArea">
                        <div class="upload-icon">📁</div>
                        <div class="upload-text">Klik untuk memilih file atau drag & drop</div>
                        <div class="upload-subtext">
                            Pilih file kuesioner yang telah diisi. Maksimal 10MB per file.<br>
                            Format yang didukung: Excel (.xlsx, .xls), PDF, Word (.doc, .docx)<br>
                            Anda dapat memilih multiple file sekaligus.
                        </div>
                        <input type="file" id="files" name="files[]" multiple accept=".xlsx,.xls,.pdf,.doc,.docx" class="form-control" style="display: none;">
                    </div>
                    <div id="fileList" class="mt-3"></div>
                    <div id="fileError" class="error-message" style="display: none;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="15" y1="9" x2="9" y2="15"></line>
                            <line x1="9" y1="9" x2="15" y2="15"></line>
                        </svg>
                        File kuesioner wajib diupload.
                    </div>
                    @error('files.*')
                        <div class="error-message">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                            {{ $message }}
                        </div>
                    @enderror
                    <div id="fileValidationError" class="error-message" style="display: none; margin-top: 0.5rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="15" y1="9" x2="9" y2="15"></line>
                            <line x1="9" y1="9" x2="15" y2="15"></line>
                        </svg>
                        <span id="fileValidationMessage"><!-- Dynamic validation errors will be shown here --></span>
                    </div>
                    @error('files')
                        <div class="error-message">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="form-actions">
                    <!-- Helper text for validation errors -->
                    <div id="submitHelperText" class="submit-helper-text" style="display: none;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#dc3545" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        Perbaiki file yang bermasalah (ditandai merah) sebelum mengirim survei
                    </div>
                    
                    <button type="submit" id="submitBtn" class="survey-submit-btn" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22,4 12,14.01 9,11.01"></polyline>
                        </svg>
                        Kirim Survei
                    </button>
                </div>

                <div class="required-note">
                    * Semua field yang bertanda bintang wajib diisi
                </div>
            </form>
        </div>
    </div>

    <!-- Contact Information Section -->
    <div class="contact-section" data-aos="fade-up" data-aos-delay="400">
        <h3 class="contact-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
            </svg>
            Butuh Bantuan?
        </h3>
        <div class="contact-content">
            <strong>Hubungi kami jika Anda memerlukan bantuan:</strong><br><br>
            📧 Email: <a href="mailto:produksi2171@bps.go.id">produksi2171@bps.go.id</a> | <a href="mailto:produksi21713@gmail.com">produksi21713@gmail.com</a><br>
            👤 Kontak Personal: Ridha Amalia Hakim / 085255557116<br>
            📞 Telepon Kantor: (0778) 5508877<br>
            💬 WhatsApp Bot: <a href="https://wa.me/6281319992171" target="_blank">Encik Bot</a>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // File upload functionality
    const fileUploadArea = document.getElementById('fileUploadArea');
    const fileInput = document.getElementById('files');
    const fileList = document.getElementById('fileList');

    // Click to select files
    fileUploadArea.addEventListener('click', function() {
        fileInput.click();
    });

    // Drag and drop functionality
    fileUploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        fileUploadArea.style.borderColor = 'var(--accent-color)';
        fileUploadArea.style.background = '#eff6ff';
    });

    fileUploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        fileUploadArea.style.borderColor = '#cbd5e1';
        fileUploadArea.style.background = '#f8fafc';
    });

    fileUploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        fileUploadArea.style.borderColor = '#cbd5e1';
        fileUploadArea.style.background = '#f8fafc';

        const files = e.dataTransfer.files;
        fileInput.files = files;
        displayFileList(files);
    });

    // File input change with validation
    fileInput.addEventListener('change', function() {
        displayFileList(this.files);
        validateFiles();
        validateForm();
    });

    // Form validation
    const surveyForm = document.getElementById('surveyForm');
    if (surveyForm) {
        surveyForm.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                return false;
            }
        });
    }

    // Comprehensive file validation function
    function validateFiles() {
        const files = fileInput.files;
        const fileError = document.getElementById('fileError');
        const fileValidationError = document.getElementById('fileValidationError');
        const fileValidationMessage = document.getElementById('fileValidationMessage');
        const submitHelperText = document.getElementById('submitHelperText');

        // Reset error states
        fileError.style.display = 'none';
        fileValidationError.style.display = 'none';
        submitHelperText.style.display = 'none';

        // Check if files are selected
        if (!files || files.length === 0) {
            fileError.style.display = 'block';
            return false;
        }

        // Validate each file
        const maxSize = 10 * 1024 * 1024; // 10MB
        const allowedTypes = ['.xlsx', '.xls', '.pdf', '.doc', '.docx'];
        const errors = [];

        for (let i = 0; i < files.length; i++) {
            const file = files[i];

            // Check file size
            if (file.size > maxSize) {
                errors.push(`File "${file.name}" melebihi batas ukuran 10MB (${(file.size / 1024 / 1024).toFixed(2)}MB)`);
            }

            // Check file type - ensure case-insensitive comparison
            const fileName = file.name.toLowerCase();
            const hasValidExtension = allowedTypes.some(type => fileName.endsWith(type.toLowerCase()));
            if (!hasValidExtension) {
                errors.push(`File "${file.name}" memiliki format yang tidak didukung. Gunakan format: Excel (.xlsx, .xls), PDF, atau Word (.doc, .docx)`);
            }
        }

        if (errors.length > 0) {
            fileValidationMessage.innerHTML = errors.join('<br>');
            fileValidationError.style.display = 'block';
            submitHelperText.style.display = 'flex';

            // Scroll to error
            fileValidationError.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });

            return false;
        }

        return true;
    }

    // Comprehensive form validation function
    function validateForm() {
        const submitBtn = document.getElementById('submitBtn');
        const submitHelperText = document.getElementById('submitHelperText');
        let isValid = true;
        let errorMessages = [];

        // Check required fields
        const requiredFields = [
            { name: 'nama', label: 'Nama Lengkap' },
            { name: 'jabatan', label: 'Jabatan' },
            { name: 'no_hp', label: 'Nomor HP' },
            { name: 'email', label: 'Alamat Email' },
            { name: 'perusahaan', label: 'Nama Perusahaan' }
        ];

        for (const field of requiredFields) {
            const element = document.getElementById(field.name) || document.querySelector(`[name="${field.name}"]`);
            if (!element || !element.value.trim()) {
                isValid = false;
                errorMessages.push(`${field.label} harus diisi`);
            }
        }

        // Special validation for jenis_perusahaan radio buttons
        const jenisPerusahaanRadios = document.querySelectorAll('input[name="jenis_perusahaan"]');
        const jenisPerusahaanSelected = Array.from(jenisPerusahaanRadios).some(radio => radio.checked);
        if (!jenisPerusahaanSelected) {
            isValid = false;
            errorMessages.push('Jenis Perusahaan harus dipilih');
        }

        // Check if alamat is required when adding new company
        if (isManualEntry) {
            const alamatField = document.getElementById('alamat');
            if (!alamatField || !alamatField.value.trim()) {
                isValid = false;
                errorMessages.push('Alamat Perusahaan harus diisi untuk perusahaan baru');
            }
        }

        // Check company data completion
        const perusahaanField = document.getElementById('perusahaan');
        const companyIdField = document.getElementById('company_id');
        if (perusahaanField && perusahaanField.value.trim() && !companyIdField.value && !isManualEntry) {
            isValid = false;
            errorMessages.push('Klik tombol "Tambah Perusahaan" untuk menyimpan data perusahaan');
        }

        // Check file validation
        if (!validateFiles()) {
            isValid = false;
            errorMessages.push('File kuesioner harus diunggah (format: PDF, DOC, DOCX, XLS, XLSX, maksimal 10MB)');
        }

        // Update submit button state and helper text
        if (isValid) {
            submitBtn.disabled = false;
            submitBtn.style.opacity = '1';
            submitBtn.style.cursor = 'pointer';
            submitHelperText.style.display = 'none';
        } else {
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.6';
            submitBtn.style.cursor = 'not-allowed';
            
            // Show specific error messages
            if (errorMessages.length > 0) {
                submitHelperText.innerHTML = `
                    <div style="color: #dc2626; font-size: 0.875rem; margin-top: 0.5rem;">
                        <div style="font-weight: 600; margin-bottom: 0.25rem;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline; margin-right: 4px;">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                            Lengkapi data berikut untuk melanjutkan:
                        </div>
                        <ul style="margin: 0; padding-left: 1.5rem; list-style-type: disc;">
                            ${errorMessages.map(msg => `<li>${msg}</li>`).join('')}
                        </ul>
                    </div>
                `;
                submitHelperText.style.display = 'block';
            }
        }

        return isValid;
    }

    // Store selected files array for manipulation
    let selectedFiles = [];

    // Display selected files with CRUD functionality
    function displayFileList(files) {
        // Update selectedFiles array
        selectedFiles = Array.from(files);
        
        fileList.innerHTML = '';

        if (selectedFiles.length > 0) {
            const listContainer = document.createElement('div');
            listContainer.className = 'selected-files';
            listContainer.innerHTML = '<h4 style="margin-bottom: 1rem; color: var(--text-primary); font-weight: 600;">File yang dipilih:</h4>';

            selectedFiles.forEach((file, index) => {
                // Validate individual file
                const maxSize = 10 * 1024 * 1024; // 10MB
                const allowedTypes = ['.xlsx', '.xls', '.pdf', '.doc', '.docx'];
                const fileName = file.name.toLowerCase();
                const hasValidExtension = allowedTypes.some(type => fileName.endsWith(type.toLowerCase()));
                const isValidSize = file.size <= maxSize;
                const isValid = hasValidExtension && isValidSize;
                
                // Determine error message
                let errorMessage = '';
                if (!isValidSize) {
                    errorMessage = `Ukuran terlalu besar: ${(file.size / 1024 / 1024).toFixed(2)}MB / 10MB max`;
                } else if (!hasValidExtension) {
                    errorMessage = 'Format tidak valid';
                }

                const fileItem = document.createElement('div');
                fileItem.className = 'file-item';
                
                // Dynamic styling based on validation status
                const baseStyle = `
                    display: flex;
                    align-items: center;
                    gap: 0.75rem;
                    padding: 0.75rem;
                    border-radius: 0.5rem;
                    margin-bottom: 0.5rem;
                    transition: all 0.3s ease;
                    position: relative;
                `;
                
                const validStyle = `
                    ${baseStyle}
                    background: #f0fdf4;
                    border: 1px solid #bbf7d0;
                `;
                
                const invalidStyle = `
                    ${baseStyle}
                    background: #fef2f2;
                    border: 2px solid #ef4444;
                    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
                `;
                
                fileItem.style.cssText = isValid ? validStyle : invalidStyle;

                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                
                // Status icon - checkmark for valid, warning for invalid
                const statusIcon = isValid ? 
                    `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 12l2 2 4-4"></path>
                        <circle cx="12" cy="12" r="10"></circle>
                    </svg>` :
                    `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
                        <path d="M12 9v4"/>
                        <path d="m12 17 .01 0"/>
                    </svg>`;

                // File type icon
                const fileIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14,2 14,8 20,8"></polyline>
                </svg>`;

                // Action buttons with enhanced styling for invalid files
                const actionButtonsStyle = isValid ? 
                    'display: flex; gap: 0.5rem;' :
                    'display: flex; gap: 0.5rem; animation: pulse 2s infinite;';

                const replaceButtonStyle = isValid ?
                    `display: inline-flex;
                    align-items: center;
                    gap: 0.25rem;
                    padding: 0.5rem 0.75rem;
                    background: #f59e0b;
                    color: white;
                    border: none;
                    border-radius: 0.375rem;
                    font-size: 0.875rem;
                    font-weight: 500;
                    cursor: pointer;
                    transition: all 0.2s ease;` :
                    `display: inline-flex;
                    align-items: center;
                    gap: 0.25rem;
                    padding: 0.5rem 0.75rem;
                    background: #dc2626;
                    color: white;
                    border: none;
                    border-radius: 0.375rem;
                    font-size: 0.875rem;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.2s ease;
                    box-shadow: 0 0 0 2px rgba(220, 38, 38, 0.2);`;

                const deleteButtonStyle = isValid ?
                    `display: inline-flex;
                    align-items: center;
                    gap: 0.25rem;
                    padding: 0.5rem 0.75rem;
                    background: #ef4444;
                    color: white;
                    border: none;
                    border-radius: 0.375rem;
                    font-size: 0.875rem;
                    font-weight: 500;
                    cursor: pointer;
                    transition: all 0.2s ease;` :
                    `display: inline-flex;
                    align-items: center;
                    gap: 0.25rem;
                    padding: 0.5rem 0.75rem;
                    background: #dc2626;
                    color: white;
                    border: none;
                    border-radius: 0.375rem;
                    font-size: 0.875rem;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.2s ease;
                    box-shadow: 0 0 0 2px rgba(220, 38, 38, 0.2);`;

                fileItem.innerHTML = `
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        ${statusIcon}
                        ${fileIcon}
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 500; color: ${isValid ? 'var(--text-primary)' : '#dc2626'};">${file.name}</div>
                        <div style="font-size: 0.875rem; color: var(--text-secondary);">${fileSize} MB</div>
                        ${!isValid ? `<div style="font-size: 0.8125rem; color: #dc2626; font-weight: 500; margin-top: 0.25rem;">${errorMessage}</div>` : ''}
                    </div>
                    <div class="file-actions" style="${actionButtonsStyle}">
                        <button type="button" class="btn-replace" data-index="${index}" style="${replaceButtonStyle}" title="Ganti file ini">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14,2 14,8 20,8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                            </svg>
                            Ganti
                        </button>
                        <button type="button" class="btn-delete" data-index="${index}" style="${deleteButtonStyle}" title="Hapus file ini">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3,6 5,6 21,6"></polyline>
                                <path d="m19,6v14a2,2 0 0,1 -2,2H7a2,2 0 0,1 -2,-2V6m3,0V4a2,2 0 0,1 2,-2h4a2,2 0 0,1 2,2v2"></path>
                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                <line x1="14" y1="11" x2="14" y2="17"></line>
                            </svg>
                            Hapus
                        </button>
                    </div>
                `;

                listContainer.appendChild(fileItem);
            });

            fileList.appendChild(listContainer);

            // Add event listeners for action buttons
            addFileActionListeners();
        }

        // Trigger validation after displaying files
        validateFiles();
        validateForm();
    }

    // Add event listeners for file action buttons
    function addFileActionListeners() {
        // Delete button listeners
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function() {
                const index = parseInt(this.getAttribute('data-index'));
                deleteFile(index);
            });

            // Hover effects
            button.addEventListener('mouseenter', function() {
                this.style.background = '#dc2626';
                this.style.transform = 'translateY(-1px)';
            });
            button.addEventListener('mouseleave', function() {
                this.style.background = '#ef4444';
                this.style.transform = 'translateY(0)';
            });
        });

        // Replace button listeners
        document.querySelectorAll('.btn-replace').forEach(button => {
            button.addEventListener('click', function() {
                const index = parseInt(this.getAttribute('data-index'));
                replaceFile(index);
            });

            // Hover effects
            button.addEventListener('mouseenter', function() {
                this.style.background = '#d97706';
                this.style.transform = 'translateY(-1px)';
            });
            button.addEventListener('mouseleave', function() {
                this.style.background = '#f59e0b';
                this.style.transform = 'translateY(0)';
            });
        });
    }

    // Delete file function
     function deleteFile(index) {
         const fileName = selectedFiles[index].name;
         showCustomConfirm(
             'Hapus File',
             `Apakah Anda yakin ingin menghapus file "${fileName}"?`,
             'File yang dihapus tidak dapat dikembalikan.',
             function() {
                 selectedFiles.splice(index, 1);
                 updateFileInput();
                 displayFileList(selectedFiles);

                 // Show success notification
                 showNotification('File berhasil dihapus', 'success');
             }
         );
     }

     // Custom confirmation dialog
     function showCustomConfirm(title, message, subtitle, onConfirm) {
         // Create overlay
         const overlay = document.createElement('div');
         overlay.style.cssText = `
             position: fixed;
             top: 0;
             left: 0;
             width: 100%;
             height: 100%;
             background: rgba(0, 0, 0, 0.5);
             display: flex;
             align-items: center;
             justify-content: center;
             z-index: 10000;
             backdrop-filter: blur(4px);
         `;

         // Create dialog
         const dialog = document.createElement('div');
         dialog.style.cssText = `
             background: white;
             border-radius: 1rem;
             padding: 2rem;
             max-width: 400px;
             width: 90%;
             box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
             transform: scale(0.9);
             transition: transform 0.2s ease;
         `;

         dialog.innerHTML = `
             <div style="text-align: center; margin-bottom: 1.5rem;">
                 <div style="
                     width: 4rem;
                     height: 4rem;
                     background: #fef2f2;
                     border-radius: 50%;
                     display: flex;
                     align-items: center;
                     justify-content: center;
                     margin: 0 auto 1rem;
                 ">
                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                         <polyline points="3,6 5,6 21,6"></polyline>
                         <path d="m19,6v14a2,2 0 0,1 -2,2H7a2,2 0 0,1 -2,-2V6m3,0V4a2,2 0 0,1 2,-2h4a2,2 0 0,1 2,2v2"></path>
                         <line x1="10" y1="11" x2="10" y2="17"></line>
                         <line x1="14" y1="11" x2="14" y2="17"></line>
                     </svg>
                 </div>
                 <h3 style="
                     font-size: 1.25rem;
                     font-weight: 600;
                     color: #1f2937;
                     margin: 0 0 0.5rem 0;
                 ">${title}</h3>
                 <p style="
                     color: #6b7280;
                     margin: 0 0 0.5rem 0;
                     line-height: 1.5;
                 ">${message}</p>
                 <p style="
                     color: #9ca3af;
                     font-size: 0.875rem;
                     margin: 0;
                     font-style: italic;
                 ">${subtitle}</p>
             </div>
             <div style="display: flex; gap: 0.75rem; justify-content: center;">
                 <button id="cancelBtn" style="
                     padding: 0.75rem 1.5rem;
                     background: #f3f4f6;
                     color: #374151;
                     border: none;
                     border-radius: 0.5rem;
                     font-weight: 500;
                     cursor: pointer;
                     transition: all 0.2s ease;
                     min-width: 100px;
                 ">Batal</button>
                 <button id="confirmBtn" style="
                     padding: 0.75rem 1.5rem;
                     background: #ef4444;
                     color: white;
                     border: none;
                     border-radius: 0.5rem;
                     font-weight: 500;
                     cursor: pointer;
                     transition: all 0.2s ease;
                     min-width: 100px;
                 ">Hapus</button>
             </div>
         `;

         overlay.appendChild(dialog);
         document.body.appendChild(overlay);

         // Animate in
         setTimeout(() => {
             dialog.style.transform = 'scale(1)';
         }, 10);

         // Button event listeners
         const cancelBtn = dialog.querySelector('#cancelBtn');
         const confirmBtn = dialog.querySelector('#confirmBtn');

         // Hover effects
         cancelBtn.addEventListener('mouseenter', () => {
             cancelBtn.style.background = '#e5e7eb';
         });
         cancelBtn.addEventListener('mouseleave', () => {
             cancelBtn.style.background = '#f3f4f6';
         });

         confirmBtn.addEventListener('mouseenter', () => {
             confirmBtn.style.background = '#dc2626';
         });
         confirmBtn.addEventListener('mouseleave', () => {
             confirmBtn.style.background = '#ef4444';
         });

         // Close dialog function
         function closeDialog() {
             dialog.style.transform = 'scale(0.9)';
             overlay.style.opacity = '0';
             setTimeout(() => {
                 document.body.removeChild(overlay);
             }, 200);
         }

         // Event listeners
         cancelBtn.addEventListener('click', closeDialog);
         overlay.addEventListener('click', (e) => {
             if (e.target === overlay) closeDialog();
         });

         confirmBtn.addEventListener('click', () => {
             closeDialog();
             onConfirm();
         });

         // ESC key to close
         const escHandler = (e) => {
             if (e.key === 'Escape') {
                 closeDialog();
                 document.removeEventListener('keydown', escHandler);
             }
         };
         document.addEventListener('keydown', escHandler);
     }

     // Success/Error notification
     function showNotification(message, type = 'success') {
         const notification = document.createElement('div');
         notification.style.cssText = `
             position: fixed;
             top: 2rem;
             right: 2rem;
             background: ${type === 'success' ? '#10b981' : '#ef4444'};
             color: white;
             padding: 1rem 1.5rem;
             border-radius: 0.75rem;
             box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
             z-index: 10001;
             display: flex;
             align-items: center;
             gap: 0.75rem;
             font-weight: 500;
             transform: translateX(100%);
             transition: transform 0.3s ease;
         `;

         const icon = type === 'success' ? 
             `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                 <polyline points="9,11 12,14 22,4"></polyline>
                 <path d="m21,12v7a2,2 0 0,1 -2,2H5a2,2 0 0,1 -2,-2V5a2,2 0 0,1 2,-2h11"></path>
             </svg>` :
             `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                 <circle cx="12" cy="12" r="10"></circle>
                 <line x1="15" y1="9" x2="9" y2="15"></line>
                 <line x1="9" y1="9" x2="15" y2="15"></line>
             </svg>`;

         notification.innerHTML = `${icon}<span>${message}</span>`;
         document.body.appendChild(notification);

         // Animate in
         setTimeout(() => {
             notification.style.transform = 'translateX(0)';
         }, 100);

         // Auto remove after 3 seconds
         setTimeout(() => {
             notification.style.transform = 'translateX(100%)';
             setTimeout(() => {
                 if (document.body.contains(notification)) {
                     document.body.removeChild(notification);
                 }
             }, 300);
         }, 3000);
     }

    // Replace file function
    function replaceFile(index) {
        const tempInput = document.createElement('input');
        tempInput.type = 'file';
        tempInput.accept = '.xlsx,.xls,.pdf,.doc,.docx';
        tempInput.style.display = 'none';
        
        tempInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const newFile = this.files[0];

                // Validate file size (10MB limit)
                if (newFile.size > 10 * 1024 * 1024) {
                    showNotification('Ukuran file tidak boleh lebih dari 10MB', 'error');
                    return;
                }

                // Validate file type
                const fileName = newFile.name.toLowerCase();
                const allowedTypes = ['.xlsx', '.xls', '.pdf', '.doc', '.docx'];
                const hasValidExtension = allowedTypes.some(type => fileName.endsWith(type.toLowerCase()));
                if (!hasValidExtension) {
                    showNotification('Format file tidak didukung. Gunakan format: Excel (.xlsx, .xls), PDF, atau Word (.doc, .docx)', 'error');
                    return;
                }

                // Replace the file at the specified index
                selectedFiles[index] = newFile;
                updateFileInput();
                displayFileList(selectedFiles);

                showNotification('File berhasil diganti', 'success');
            }
            document.body.removeChild(tempInput);
        });
        
        document.body.appendChild(tempInput);
        tempInput.click();
    }

    // Update the actual file input with selected files
    function updateFileInput() {
        const dt = new DataTransfer();
        selectedFiles.forEach(file => {
            dt.items.add(file);
        });
        fileInput.files = dt.files;
    }

    // Company search functionality
    const companySearch = document.getElementById('company_search');
    const companyDropdown = document.getElementById('company_dropdown');
    const companyIdInput = document.getElementById('company_id');
    const perusahaanInput = document.getElementById('perusahaan');
    const alamatInput = document.getElementById('alamat');
    const addCompanyBtn = document.getElementById('add_company_btn');

    let searchTimeout;
    let isManualEntry = false;

    companySearch.addEventListener('input', function() {
        const query = this.value.trim();

        clearTimeout(searchTimeout);

        if (query.length < 2) {
            companyDropdown.style.display = 'none';
            addCompanyBtn.style.display = 'none';
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch(`{{ route('temporary.survey.sibstr.companies.search') }}?search=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    displayCompanyResults(data.companies, query);
                })
                .catch(error => {
                    console.error('Error searching companies:', error);
                });
        }, 300);
    });

    function displayCompanyResults(companies, query) {
        companyDropdown.innerHTML = '';

        // Always show existing companies first if any
        if (companies.length > 0) {
            companies.forEach(company => {
                const item = document.createElement('div');
                item.className = 'company-dropdown-item';
                item.style.cssText = 'padding: 12px; cursor: pointer; border-bottom: 1px solid #f3f4f6; hover:background-color: #f9fafb;';
                item.innerHTML = `
                    <div style="font-weight: 500; color: #1f2937;">${company.nama_perusahaan}</div>
                    <div style="font-size: 0.875rem; color: #6b7280; margin-top: 2px;">${company.alamat || '-'}</div>
                `;

                item.addEventListener('click', () => {
                    selectCompany(company);
                });

                item.addEventListener('mouseenter', () => {
                    item.style.backgroundColor = '#f9fafb';
                });

                item.addEventListener('mouseleave', () => {
                    item.style.backgroundColor = 'white';
                });

                companyDropdown.appendChild(item);
            });
        }

        // Always show "Add New Company" option when user has typed 5 or more characters
        if (query.length >= 5) {
            const addItem = document.createElement('div');
            addItem.className = 'company-dropdown-item';
            addItem.style.cssText = 'padding: 12px; cursor: pointer; background-color: #f0f9ff; color: #1e40af; border-top: 2px solid #e5e7eb;';
            addItem.innerHTML = `
                <div style="font-weight: 500; display: flex; align-items: center;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="16"></line>
                        <line x1="8" y1="12" x2="16" y2="12"></line>
                    </svg>
                    Tambah "${query}" sebagai perusahaan baru
                </div>
                <div style="font-size: 0.875rem; color: #6b7280; margin-top: 2px;">Klik untuk menambahkan perusahaan baru</div>
            `;

            addItem.addEventListener('click', () => {
                addNewCompany(query);
            });

            companyDropdown.appendChild(addItem);
            addCompanyBtn.style.display = 'inline-block';
        } else {
            addCompanyBtn.style.display = 'none';
        }

        // Show dropdown if there are companies or if query is 5+ characters (for "Add New Company" option)
        if (companies.length > 0 || query.length >= 5) {
            companyDropdown.style.display = 'block';
        } else {
            companyDropdown.style.display = 'none';
        }
    }

    function selectCompany(company) {
        companyIdInput.value = company.id;
        perusahaanInput.value = company.nama_perusahaan;
        alamatInput.value = company.alamat;
        companySearch.value = company.nama_perusahaan;
        companyDropdown.style.display = 'none';
        addCompanyBtn.style.display = 'none';
        isManualEntry = false;

        // Make alamat field readonly when company is selected
        alamatInput.readOnly = true;
        alamatInput.style.backgroundColor = '#f9fafb';
        
        // Update address field indicators
        const alamatRequiredIndicator = document.getElementById('alamat-required-indicator');
        const alamatOptionalIndicator = document.getElementById('alamat-optional-indicator');
        const alamatHelper = document.getElementById('alamatHelper');
        const alamatHelperExisting = document.getElementById('alamatHelperExisting');
        const alamatHelperNew = document.getElementById('alamatHelperNew');
        
        if (alamatRequiredIndicator) alamatRequiredIndicator.style.display = 'none';
        if (alamatOptionalIndicator) alamatOptionalIndicator.style.display = 'inline';
        if (alamatHelper) alamatHelper.style.display = 'block';
        if (alamatHelperExisting) alamatHelperExisting.style.display = 'block';
        if (alamatHelperNew) alamatHelperNew.style.display = 'none';
        
        // Remove required attribute
        alamatInput.removeAttribute('required');
        
        // Update company status indicators
        const companyStatusSelected = document.getElementById('companyStatusSelected');
        const companyStatusNew = document.getElementById('companyStatusNew');
        if (companyStatusSelected) companyStatusSelected.style.display = 'block';
        if (companyStatusNew) companyStatusNew.style.display = 'none';
        
        // Trigger validation
        validateForm();
    }

    function addNewCompany(companyName) {
        companyIdInput.value = '';
        perusahaanInput.value = companyName;
        companySearch.value = companyName;
        alamatInput.value = '';
        companyDropdown.style.display = 'none';
        addCompanyBtn.style.display = 'none';
        isManualEntry = true;

        // Make alamat field editable for new company
        alamatInput.readOnly = false;
        alamatInput.style.backgroundColor = 'white';
        alamatInput.focus();
        
        // Update address field indicators
        const alamatRequiredIndicator = document.getElementById('alamat-required-indicator');
        const alamatOptionalIndicator = document.getElementById('alamat-optional-indicator');
        const alamatHelper = document.getElementById('alamatHelper');
        const alamatHelperExisting = document.getElementById('alamatHelperExisting');
        const alamatHelperNew = document.getElementById('alamatHelperNew');
        
        if (alamatRequiredIndicator) alamatRequiredIndicator.style.display = 'inline';
        if (alamatOptionalIndicator) alamatOptionalIndicator.style.display = 'none';
        if (alamatHelper) alamatHelper.style.display = 'block';
        if (alamatHelperExisting) alamatHelperExisting.style.display = 'none';
        if (alamatHelperNew) alamatHelperNew.style.display = 'block';
        
        // Add required attribute
        alamatInput.setAttribute('required', 'required');
        
        // Update company status indicators
        const companyStatusSelected = document.getElementById('companyStatusSelected');
        const companyStatusNew = document.getElementById('companyStatusNew');
        if (companyStatusSelected) companyStatusSelected.style.display = 'none';
        if (companyStatusNew) companyStatusNew.style.display = 'block';
        
        // Trigger validation to update submit button state
        validateForm();
    }

    // Handle add company button click
    addCompanyBtn.addEventListener('click', function() {
        addNewCompany(companySearch.value);
    });

    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!companySearch.contains(e.target) && !companyDropdown.contains(e.target)) {
            companyDropdown.style.display = 'none';
        }
    });

    // Add form field listeners for real-time validation
    const requiredFields = ['nama', 'jabatan', 'no_hp', 'email'];

    requiredFields.forEach(fieldName => {
        const field = document.getElementById(fieldName) || document.querySelector(`[name="${fieldName}"]`);
        if (field) {
            field.addEventListener('input', validateForm);
            field.addEventListener('change', validateForm);
        }
    });

    // Special listener for jenis_perusahaan radio buttons
    const jenisPerusahaanRadios = document.querySelectorAll('input[name="jenis_perusahaan"]');
    jenisPerusahaanRadios.forEach(radio => {
        radio.addEventListener('change', validateForm);
    });

    // Special listeners for company and address fields
    if (perusahaanInput) {
        perusahaanInput.addEventListener('input', validateForm);
        perusahaanInput.addEventListener('change', validateForm);
    }

    if (alamatInput) {
        alamatInput.addEventListener('input', validateForm);
        alamatInput.addEventListener('change', validateForm);
    }

    if (companySearch) {
        companySearch.addEventListener('input', validateForm);
        companySearch.addEventListener('change', validateForm);
    }

    // Phone number validation - restrict to numeric characters only
    const phoneField = document.getElementById('no_hp');
    if (phoneField) {
        // Prevent non-numeric characters from being typed
        phoneField.addEventListener('keypress', function(e) {
            // Allow backspace, delete, tab, escape, enter
            if ([8, 9, 27, 13, 46].indexOf(e.keyCode) !== -1 ||
                // Allow Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                (e.keyCode === 65 && e.ctrlKey === true) ||
                (e.keyCode === 67 && e.ctrlKey === true) ||
                (e.keyCode === 86 && e.ctrlKey === true) ||
                (e.keyCode === 88 && e.ctrlKey === true)) {
                return;
            }
            
            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
                
                // Show visual feedback for invalid input
                phoneField.style.borderColor = '#ef4444';
                phoneField.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.1)';
                
                // Reset border color after a short delay
                setTimeout(() => {
                    phoneField.style.borderColor = '';
                    phoneField.style.boxShadow = '';
                }, 500);
            }
        });

        // Additional validation on input event to handle paste operations
        phoneField.addEventListener('input', function(e) {
            // Remove any non-numeric characters
            let value = this.value.replace(/[^0-9]/g, '');
            
            // Update the field value if it was changed
            if (value !== this.value) {
                this.value = value;
                
                // Show visual feedback for cleaned input
                this.style.borderColor = '#f59e0b';
                this.style.boxShadow = '0 0 0 3px rgba(245, 158, 11, 0.1)';
                
                // Reset border color after a short delay
                setTimeout(() => {
                    this.style.borderColor = '';
                    this.style.boxShadow = '';
                }, 500);
            }
        });

        // Prevent paste of non-numeric content
        phoneField.addEventListener('paste', function(e) {
            e.preventDefault();
            
            // Get pasted data
            let paste = (e.clipboardData || window.clipboardData).getData('text');
            
            // Remove non-numeric characters
            let numericOnly = paste.replace(/[^0-9]/g, '');
            
            // Insert the cleaned numeric content
            if (numericOnly) {
                // Get current cursor position
                let start = this.selectionStart;
                let end = this.selectionEnd;
                
                // Replace selected text with numeric content
                let currentValue = this.value;
                this.value = currentValue.substring(0, start) + numericOnly + currentValue.substring(end);
                
                // Set cursor position after inserted text
                this.setSelectionRange(start + numericOnly.length, start + numericOnly.length);
                
                // Trigger input event for validation
                this.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });
    }

    // Initial validation check
    validateForm();

    // Form reset after successful submission
    @if(session('success'))
        // Reset form after successful submission
        document.getElementById('surveyForm').reset();
        fileList.innerHTML = '';

        // Scroll to top to show success message
        window.scrollTo({ top: 0, behavior: 'smooth' });
    @endif
});
</script>
@endpush

@endsection
