<?php
require_once 'config/database.php';
require_once 'config/check_login.php';

// Fetch active banners/promotions from database
$banner_query = "SELECT * FROM banners WHERE active = 1 ORDER BY created_at DESC";
$banner_result = $conn->query($banner_query);
$banners = $banner_result->fetch_all(MYSQLI_ASSOC);

// Fetch featured products on promo
$promo_products_query = "
    SELECT * FROM products 
    WHERE featured = 1 OR popular = 1 
    ORDER BY RAND() 
    LIMIT 8
";
$promo_products_result = $conn->query($promo_products_query);
$promo_products = $promo_products_result->fetch_all(MYSQLI_ASSOC);

// Fetch all categories
$categories_query = "SELECT DISTINCT category FROM products WHERE category IS NOT NULL";
$categories_result = $conn->query($categories_query);
$categories = $categories_result->fetch_all(MYSQLI_ASSOC);

// Current date for promo validation
$current_date = date('Y-m-d H:i:s');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promo & Penawaran - Megatek Industrial Persada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    
    <style>
        :root {
            --primary-blue: #1a4b8c;
            --secondary-blue: #2c6cb0;
            --accent-orange: #ff6b35;
            --accent-green: #28a745;
            --light-gray: #f8f9fa;
            --dark-gray: #222;
            --medium-gray: #666;
        }

        body {
            font-family: "Poppins", sans-serif;
            background-color: var(--light-gray);
            color: var(--dark-gray);
            margin: 0;
            padding: 0;
        }

        /* Reuse all common styles from cart.php */
        .top-bar {
            background-color: #f0f2f5;
            padding: 5px 0;
            font-size: 12px;
            border-bottom: 1px solid #e0e0e0;
        }

        .top-bar-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .top-bar-links {
            display: flex;
            gap: 20px;
        }

        .top-bar-links a {
            color: #666;
            text-decoration: none;
            transition: color 0.3s;
        }

        .top-bar-links a:hover {
            color: var(--primary-blue);
        }

        .app-promo {
            display: flex;
            align-items: center;
            gap: 5px;
            color: var(--primary-blue);
            font-weight: 500;
        }

        /* Main Navbar */
        .navbar {
            background-color: white;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            padding: 10px 20px;
        }

        .navbar-brand img {
            height: 40px;
        }

        .search-bar {
            flex-grow: 1;
            max-width: 500px;
            margin: 0 auto;
            position: relative;
        }

        .search-bar input {
            border-radius: 20px;
            border: 1px solid #ddd;
            padding: 10px 45px 10px 20px;
            font-size: 14px;
            width: 100%;
            background-color: #f8f9fa;
        }

        .search-bar button {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
        }

        .nav-icons {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .nav-icon {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #666;
            text-decoration: none;
            font-size: 12px;
            min-width: 50px;
        }

        .nav-icon i {
            font-size: 20px;
            margin-bottom: 3px;
        }

        .nav-icon:hover {
            color: var(--primary-blue);
            text-decoration: none;
        }

        /* Main Menu Horizontal */
        .main-menu {
            background-color: white;
            border-bottom: 1px solid #e0e0e0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .menu-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .menu-category {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 15px 0;
            font-weight: 500;
            color: var(--dark-gray);
            text-decoration: none;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }

        .menu-category:hover {
            color: var(--primary-blue);
            border-bottom-color: var(--primary-blue);
        }

        .menu-category.active {
            color: var(--primary-blue);
            border-bottom-color: var(--primary-blue);
        }

        .menu-category i {
            font-size: 14px;
            margin-left: 5px;
        }

        /* Promo Hero Section */
        .promo-hero {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .promo-hero:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 600" opacity="0.1"><path fill="white" d="M0 0h1200v600H0z"/><circle cx="300" cy="300" r="150" fill="none" stroke="white" stroke-width="2"/><circle cx="900" cy="300" r="150" fill="none" stroke="white" stroke-width="2"/></svg>');
            background-size: cover;
        }

        .promo-hero-content {
            position: relative;
            z-index: 1;
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .promo-hero h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 15px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .promo-hero p {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 30px;
        }

        .promo-badge {
            display: inline-block;
            background-color: var(--accent-orange);
            color: white;
            padding: 10px 25px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
        }

        /* Promo Container */
        .promo-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 15px;
        }

        /* Promo Banner Slider */
        .promo-banner-section {
            margin-bottom: 50px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .section-header h2 {
            color: var(--primary-blue);
            font-size: 1.8rem;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-header h2 i {
            color: var(--accent-orange);
        }

        .view-all {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
        }

        .view-all:hover {
            color: var(--secondary-blue);
            text-decoration: underline;
        }

        .swiper {
            width: 100%;
            height: 400px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
        }

        .swiper-slide {
            position: relative;
            overflow: hidden;
        }

        .swiper-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .swiper-slide:hover img {
            transform: scale(1.05);
        }

        .banner-content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 30px;
            background: linear-gradient(transparent, rgba(0,0,0,0.8));
            color: white;
        }

        .banner-badge {
            background-color: var(--accent-orange);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 10px;
        }

        .banner-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.5);
        }

        .banner-description {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 15px;
        }

        .banner-button {
            display: inline-block;
            background-color: white;
            color: var(--primary-blue);
            padding: 10px 25px;
            border-radius: 30px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }

        .banner-button:hover {
            background-color: var(--primary-blue);
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .swiper-button-next, .swiper-button-prev {
            color: white;
            background-color: rgba(0,0,0,0.3);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            transition: all 0.3s;
        }

        .swiper-button-next:after, .swiper-button-prev:after {
            font-size: 20px;
        }

        .swiper-button-next:hover, .swiper-button-prev:hover {
            background-color: var(--primary-blue);
        }

        /* Promo Grid */
        .promo-grid-section {
            margin-bottom: 60px;
        }

        .promo-filters {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 10px 20px;
            background-color: white;
            border: 2px solid #e0e0e0;
            border-radius: 30px;
            color: var(--medium-gray);
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-btn:hover {
            border-color: var(--primary-blue);
            color: var(--primary-blue);
            background-color: #f0f7ff;
        }

        .filter-btn.active {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
            color: white;
        }

        .promo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        .promo-card {
            background-color: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            position: relative;
        }

        .promo-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }

        .promo-badge-top {
            position: absolute;
            top: 15px;
            left: 15px;
            z-index: 2;
        }

        .badge-hot {
            background-color: #ff4444;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .badge-discount {
            background-color: var(--accent-green);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .badge-new {
            background-color: var(--accent-orange);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .promo-image {
            height: 200px;
            overflow: hidden;
            position: relative;
        }

        .promo-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .promo-card:hover .promo-image img {
            transform: scale(1.1);
        }

        .promo-countdown {
            position: absolute;
            bottom: 10px;
            left: 10px;
            right: 10px;
            background-color: rgba(0,0,0,0.7);
            color: white;
            padding: 8px;
            border-radius: 8px;
            font-size: 12px;
            display: flex;
            justify-content: center;
            gap: 5px;
        }

        .countdown-item {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .countdown-value {
            font-weight: 700;
            font-size: 14px;
            color: #ffcc00;
        }

        .countdown-label {
            font-size: 10px;
            opacity: 0.8;
        }

        .promo-content {
            padding: 20px;
        }

        .promo-category {
            color: var(--primary-blue);
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .promo-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--dark-gray);
            line-height: 1.4;
            height: 60px;
            overflow: hidden;
        }

        .promo-description {
            color: var(--medium-gray);
            font-size: 14px;
            margin-bottom: 15px;
            line-height: 1.5;
            height: 65px;
            overflow: hidden;
        }

        .promo-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .promo-period {
            font-size: 12px;
            color: #666;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .promo-code {
            background-color: #f0f7ff;
            color: var(--primary-blue);
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 12px;
            border: 1px dashed var(--primary-blue);
        }

        .promo-actions {
            display: flex;
            gap: 10px;
        }

        .btn-promo-detail {
            flex: 1;
            background-color: var(--primary-blue);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
            text-decoration: none;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .btn-promo-detail:hover {
            background-color: var(--secondary-blue);
            color: white;
            text-decoration: none;
        }

        .btn-share {
            width: 40px;
            height: 40px;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            color: var(--medium-gray);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-share:hover {
            background-color: var(--primary-blue);
            color: white;
            border-color: var(--primary-blue);
        }

        /* Flash Sale Section */
        .flash-sale-section {
            background: linear-gradient(135deg, #fff8e1 0%, #ffecb3 100%);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 60px;
            border: 2px dashed var(--accent-orange);
            position: relative;
            overflow: hidden;
        }

        .flash-sale-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .flash-sale-title {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .flash-sale-title h2 {
            color: #d84315;
            margin: 0;
            font-size: 1.8rem;
        }

        .fire-icon {
            color: #ff5722;
            font-size: 2rem;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .flash-sale-timer {
            background-color: #d84315;
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .timer-numbers {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 2px;
        }

        .flash-sale-products {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .flash-sale-product {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            transition: all 0.3s;
            position: relative;
        }

        .flash-sale-product:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }

        .sold-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: rgba(255, 87, 34, 0.9);
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            z-index: 2;
        }

        .product-image {
            height: 150px;
            overflow: hidden;
            position: relative;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-info {
            padding: 15px;
        }

        .product-name {
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
            height: 40px;
            overflow: hidden;
        }

        .price-container {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .original-price {
            color: #999;
            text-decoration: line-through;
            font-size: 13px;
        }

        .discount-price {
            color: #d84315;
            font-weight: 700;
            font-size: 16px;
        }

        .discount-percent {
            background-color: #ffebee;
            color: #d84315;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
        }

        .progress-bar-container {
            margin-bottom: 15px;
        }

        .progress-text {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            color: #666;
            margin-bottom: 5px;
        }

        .progress-bar {
            height: 6px;
            background-color: #e0e0e0;
            border-radius: 3px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background-color: #4CAF50;
            border-radius: 3px;
            transition: width 0.3s ease;
        }

        .btn-buy-now {
            width: 100%;
            background-color: #d84315;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-buy-now:hover {
            background-color: #bf360c;
        }

        /* Mega Promo Section */
        .mega-promo-section {
            margin-bottom: 60px;
        }

        .mega-promo-card {
            background: linear-gradient(135deg, var(--primary-blue) 0%, #4a90e2 100%);
            border-radius: 20px;
            overflow: hidden;
            color: white;
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 300px;
            box-shadow: 0 10px 30px rgba(26, 75, 140, 0.3);
        }

        @media (max-width: 768px) {
            .mega-promo-card {
                grid-template-columns: 1fr;
            }
        }

        .mega-promo-content {
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .mega-promo-badge {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 8px 20px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 20px;
            backdrop-filter: blur(10px);
        }

        .mega-promo-title {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 15px;
            line-height: 1.2;
        }

        .mega-promo-description {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 25px;
            line-height: 1.5;
        }

        .mega-promo-features {
            list-style: none;
            padding: 0;
            margin-bottom: 30px;
        }

        .mega-promo-features li {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .mega-promo-features i {
            color: #ffcc00;
        }

        .mega-promo-cta {
            display: inline-block;
            background-color: white;
            color: var(--primary-blue);
            padding: 15px 30px;
            border-radius: 30px;
            font-weight: 700;
            text-decoration: none;
            font-size: 1.1rem;
            transition: all 0.3s;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .mega-promo-cta:hover {
            background-color: #ffcc00;
            color: var(--primary-blue);
            transform: translateY(-3px);
            text-decoration: none;
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
        }

        .mega-promo-image {
            position: relative;
            overflow: hidden;
        }

        .mega-promo-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Newsletter Section */
        .newsletter-section {
            background-color: white;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            margin-bottom: 60px;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            border: 2px solid var(--primary-blue);
        }

        .newsletter-icon {
            font-size: 3rem;
            color: var(--primary-blue);
            margin-bottom: 20px;
        }

        .newsletter-title {
            color: var(--primary-blue);
            font-size: 1.8rem;
            margin-bottom: 15px;
        }

        .newsletter-description {
            color: var(--medium-gray);
            font-size: 1.1rem;
            margin-bottom: 30px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .newsletter-form {
            max-width: 500px;
            margin: 0 auto;
            display: flex;
            gap: 10px;
        }

        @media (max-width: 576px) {
            .newsletter-form {
                flex-direction: column;
            }
        }

        .newsletter-input {
            flex: 1;
            padding: 15px 20px;
            border: 2px solid #ddd;
            border-radius: 30px;
            font-size: 16px;
            transition: all 0.3s;
        }

        .newsletter-input:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(26, 75, 140, 0.1);
        }

        .newsletter-button {
            background-color: var(--primary-blue);
            color: white;
            border: none;
            border-radius: 30px;
            padding: 15px 30px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .newsletter-button:hover {
            background-color: var(--secondary-blue);
        }

        /* Footer */
        .footer {
            background-color: #1a1a1a;
            color: white;
            padding: 60px 0 30px;
            margin-top: 60px;
        }

        .footer-logo {
            height: 40px;
            margin-bottom: 20px;
        }

        .footer h5 {
            color: white;
            font-weight: 600;
            margin-bottom: 25px;
            font-size: 1.1rem;
        }

        .footer-links {
            list-style: none;
            padding: 0;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: #aaa;
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-links a:hover {
            color: white;
        }

        .social-icons a {
            display: inline-block;
            width: 36px;
            height: 36px;
            background: #333;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 36px;
            margin-right: 10px;
            transition: background 0.3s;
        }

        .social-icons a:hover {
            background: var(--primary-blue);
        }

        .copyright {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #333;
            color: #aaa;
            font-size: 14px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .top-bar {
                display: none;
            }
            
            .main-menu {
                display: none;
            }
            
            .search-bar {
                max-width: 200px;
            }
            
            .nav-icon span {
                display: none;
            }
            
            .promo-hero h1 {
                font-size: 2rem;
            }
            
            .promo-hero p {
                font-size: 1rem;
            }
            
            .swiper {
                height: 300px;
            }
            
            .banner-title {
                font-size: 1.4rem;
            }
            
            .promo-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
            
            .flash-sale-header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .flash-sale-timer {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 576px) {
            .nav-icons {
                gap: 10px;
            }
            
            .search-bar {
                max-width: 150px;
            }
            
            .navbar-brand img {
                height: 30px;
            }
            
            .promo-container {
                padding: 30px 15px;
            }
            
            .promo-grid {
                grid-template-columns: 1fr;
            }
            
            .promo-filters {
                justify-content: center;
            }
            
            .mega-promo-content {
                padding: 30px 20px;
            }
            
            .mega-promo-title {
                font-size: 1.8rem;
            }
            
            .newsletter-section {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>

    <!-- Main Navbar -->
    <nav class="navbar d-flex align-items-center">
        <a class="navbar-brand mx-2" href="index.php">
            <img src="gambar/LOGO.png" alt="Megatek Logo">
        </a>

        <div class="search-bar">
            <input type="text" class="form-control" placeholder="Cari produk promo...">
            <button type="button">
                <i class="fas fa-search"></i>
            </button>
        </div>

        <div class="nav-icons">
            <a href="keranjang.php" class="nav-icon">
                <div style="position: relative;">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-badge" id="cartCount">0</span>
                </div>
                <span>Cart</span>
            </a>
            
            <div id="userSection">
                <?php if (isLoggedIn()): ?>
                    <div class="user-dropdown">
                        <a href="javascript:void(0);" class="nav-icon">
                            <i class="fas fa-user"></i>
                            <span>Akun</span>
                        </a>
                    </div>
                <?php else: ?>
                    <a href="javascript:void(0);" class="nav-icon" id="userLogin">
                        <i class="fas fa-user"></i>
                        <span>Masuk/Daftar</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

     <!-- Main Menu Horizontal -->
    <div class="main-menu">
        <div class="menu-container">
            <a href="beranda.php" class="menu-category <?php echo basename($_SERVER['PHP_SELF']) == 'produk.php' ? 'active' : ''; ?>">
                <span>Beranda</span>
            </a>
            <a href="tentangkami.php" class="menu-category">Tentang Kami</a>
            <a href="produk.php" class="menu-category">Produk</a>
            <a href="hubungikami.php" class="menu-category">Hubungi Kami</a>
            <a href="promo.php" class="menu-category">Promo</a>
        </div>
    </div>
    <!-- Promo Hero Section -->
    <section class="promo-hero">
        <div class="promo-hero-content">
            <div class="promo-badge">
                <i class="fas fa-gift"></i> PROMO SPESIAL
            </div>
            <h1>Penawaran Terbaik untuk Industri Anda</h1>
            <p>Dapatkan diskon eksklusif, bundling menarik, dan penawaran spesial untuk produk-produk industrial berkualitas dari Megatek</p>
        </div>
    </section>

    <!-- Promo Container -->
    <div class="promo-container">
        <!-- Promo Banner Slider -->
        <div class="promo-banner-section">
            <div class="section-header">
                <h2><i class="fas fa-bullhorn"></i> Promo Utama</h2>
                <a href="#all-promo" class="view-all">
                    Lihat Semua <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <!-- Swiper -->
            <div class="swiper mySwiper">
                <div class="swiper-wrapper">
                    <?php if (!empty($banners)): ?>
                        <?php foreach ($banners as $banner): ?>
                            <div class="swiper-slide">
                                <img src="<?php echo htmlspecialchars($banner['image_url']); ?>" alt="<?php echo htmlspecialchars($banner['title']); ?>">
                                <div class="banner-content">
                                    <div class="banner-badge">HOT PROMO</div>
                                    <h3 class="banner-title"><?php echo htmlspecialchars($banner['title']); ?></h3>
                                    <p class="banner-description"><?php echo htmlspecialchars($banner['description']); ?></p>
                                    <a href="<?php echo htmlspecialchars($banner['link_url']); ?>" class="banner-button">
                                        <i class="fas fa-arrow-right"></i> Lihat Promo
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Default banners if no banners in database -->
                        <div class="swiper-slide">
                            <img src="https://images.unsplash.com/photo-1581094794329-c8112a89af12?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" alt="Industrial Equipment Sale">
                            <div class="banner-content">
                                <div class="banner-badge">DISKON 30%</div>
                                <h3 class="banner-title">MEGA SALE INDUSTRIAL EQUIPMENT</h3>
                                <p class="banner-description">Dapatkan diskon hingga 30% untuk semua produk boiler dan burner series</p>
                                <a href="produk.php?category=Boiler" class="banner-button">
                                    <i class="fas fa-arrow-right"></i> Beli Sekarang
                                </a>
                            </div>
                        </div>
                        
                        <div class="swiper-slide">
                            <img src="https://images.unsplash.com/photo-1581094794329-c8112a89af12?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" alt="Free Installation">
                            <div class="banner-content">
                                <div class="banner-badge">FREE INSTALL</div>
                                <h3 class="banner-title">GRATIS INSTALASI & TRAINING</h3>
                                <p class="banner-description">Beli produk di atas Rp 50 juta dapatkan gratis instalasi dan training teknis</p>
                                <a href="produk.php?featured=1" class="banner-button">
                                    <i class="fas fa-arrow-right"></i> Cek Produk
                                </a>
                            </div>
                        </div>
                        
                        <div class="swiper-slide">
                            <img src="https://images.unsplash.com/photo-1581094794329-c8112a89af12?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" alt="Sparepart Bundle">
                            <div class="banner-content">
                                <div class="banner-badge">BUNDLE OFFER</div>
                                <h3 class="banner-title">PAKET LENGKAP SPAREPART</h3>
                                <p class="banner-description">Paket sparepart lengkap dengan harga spesial untuk maintenance rutin</p>
                                <a href="produk.php?category=Sparepart" class="banner-button">
                                    <i class="fas fa-arrow-right"></i> Lihat Paket
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </div>

        <!-- Flash Sale Section -->
        <div class="flash-sale-section">
            <div class="flash-sale-header">
                <div class="flash-sale-title">
                    <div class="fire-icon">
                        <i class="fas fa-fire"></i>
                    </div>
                    <h2>FLASH SALE</h2>
                </div>
                <div class="flash-sale-timer">
                    <i class="fas fa-clock"></i>
                    <div class="timer-numbers" id="flashSaleTimer">24:00:00</div>
                    <span>BERAKHIR DALAM</span>
                </div>
            </div>
            
            <div class="flash-sale-products">
                <!-- Flash Sale Product 1 -->
                <div class="flash-sale-product">
                    <div class="sold-badge">TERJUAL 85%</div>
                    <div class="product-image">
                        <img src="gambar/sampleproduk.png" alt="Control Valve">
                    </div>
                    <div class="product-info">
                        <div class="product-name">Control Valve Presisi 4 inch</div>
                        <div class="price-container">
                            <span class="original-price">Rp 4.500.000</span>
                            <span class="discount-price">Rp 3.500.000</span>
                            <span class="discount-percent">-22%</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-text">
                                <span>Tersisa: 15 unit</span>
                                <span>Terjual: 85 unit</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 85%;"></div>
                            </div>
                        </div>
                        <button class="btn-buy-now">
                            <i class="fas fa-bolt"></i> BELI SEKARANG
                        </button>
                    </div>
                </div>
                
                <!-- Flash Sale Product 2 -->
                <div class="flash-sale-product">
                    <div class="sold-badge">TERJUAL 70%</div>
                    <div class="product-image">
                        <img src="gambar/sampleproduk2.png" alt="Boiler Steam">
                    </div>
                    <div class="product-info">
                        <div class="product-name">Boiler Steam 500 kg per jam</div>
                        <div class="price-container">
                            <span class="original-price">Rp 95.000.000</span>
                            <span class="discount-price">Rp 85.000.000</span>
                            <span class="discount-percent">-11%</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-text">
                                <span>Tersisa: 3 unit</span>
                                <span>Terjual: 7 unit</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 70%;"></div>
                            </div>
                        </div>
                        <button class="btn-buy-now">
                            <i class="fas fa-bolt"></i> BELI SEKARANG
                        </button>
                    </div>
                </div>
                
                <!-- Flash Sale Product 3 -->
                <div class="flash-sale-product">
                    <div class="sold-badge">TERJUAL 60%</div>
                    <div class="product-image">
                        <img src="gambar/sampleproduk3.png" alt="Oil Burner">
                    </div>
                    <div class="product-info">
                        <div class="product-name">Heavy Oil Burner Single Stage</div>
                        <div class="price-container">
                            <span class="original-price">Rp 5.000.000</span>
                            <span class="discount-price">Rp 4.500.000</span>
                            <span class="discount-percent">-10%</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-text">
                                <span>Tersisa: 4 unit</span>
                                <span>Terjual: 6 unit</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 60%;"></div>
                            </div>
                        </div>
                        <button class="btn-buy-now">
                            <i class="fas fa-bolt"></i> BELI SEKARANG
                        </button>
                    </div>
                </div>
                
                <!-- Flash Sale Product 4 -->
                <div class="flash-sale-product">
                    <div class="sold-badge">TERJUAL 90%</div>
                    <div class="product-image">
                        <img src="img/produk-sample.png" alt="Sparepart Kit">
                    </div>
                    <div class="product-info">
                        <div class="product-name">Kit Sparepart Lengkap untuk Burner</div>
                        <div class="price-container">
                            <span class="original-price">Rp 1.500.000</span>
                            <span class="discount-price">Rp 1.250.000</span>
                            <span class="discount-percent">-17%</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-text">
                                <span>Tersisa: 3 unit</span>
                                <span>Terjual: 27 unit</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 90%;"></div>
                            </div>
                        </div>
                        <button class="btn-buy-now">
                            <i class="fas fa-bolt"></i> BELI SEKARANG
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Promo Grid Section -->
        <div class="promo-grid-section" id="all-promo">
            <div class="section-header">
                <h2><i class="fas fa-tags"></i> Semua Promo</h2>
                <div class="view-all">
                    <i class="fas fa-filter"></i> Filter
                </div>
            </div>
            
            <div class="promo-filters">
                <button class="filter-btn active" data-filter="all">
                    <i class="fas fa-border-all"></i> Semua
                </button>
                <button class="filter-btn" data-filter="discount">
                    <i class="fas fa-percentage"></i> Diskon
                </button>
                <button class="filter-btn" data-filter="bundle">
                    <i class="fas fa-box"></i> Bundle
                </button>
                <button class="filter-btn" data-filter="freeship">
                    <i class="fas fa-shipping-fast"></i> Gratis Ongkir
                </button>
                <button class="filter-btn" data-filter="voucher">
                    <i class="fas fa-ticket-alt"></i> Voucher
                </button>
                <button class="filter-btn" data-filter="installment">
                    <i class="fas fa-credit-card"></i> Cicilan
                </button>
            </div>
            
            <div class="promo-grid">
                <!-- Promo Card 1 -->
                <div class="promo-card" data-category="discount">
                    <div class="promo-badge-top">
                        <div class="badge-hot">
                            <i class="fas fa-fire"></i> HOT
                        </div>
                    </div>
                    <div class="promo-image">
                        <img src="https://images.unsplash.com/photo-1581094794329-c8112a89af12?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Diskon Boiler">
                        <div class="promo-countdown">
                            <div class="countdown-item">
                                <span class="countdown-value">05</span>
                                <span class="countdown-label">HARI</span>
                            </div>
                            <div class="countdown-item">
                                <span class="countdown-value">12</span>
                                <span class="countdown-label">JAM</span>
                            </div>
                            <div class="countdown-item">
                                <span class="countdown-value">45</span>
                                <span class="countdown-label">MENIT</span>
                            </div>
                        </div>
                    </div>
                    <div class="promo-content">
                        <div class="promo-category">DISKON PRODUK</div>
                        <h3 class="promo-title">Diskon 15% Semua Produk Boiler Series</h3>
                        <p class="promo-description">Dapatkan diskon spesial untuk pembelian semua tipe boiler. Berlaku untuk pembelian minimal 1 unit.</p>
                        <div class="promo-meta">
                            <div class="promo-period">
                                <i class="far fa-calendar"></i> 1-31 Des 2023
                            </div>
                            <div class="promo-code">KODE: BOILER15</div>
                        </div>
                        <div class="promo-actions">
                            <a href="produk.php?category=Boiler" class="btn-promo-detail">
                                <i class="fas fa-shopping-cart"></i> Gunakan Promo
                            </a>
                            <button class="btn-share">
                                <i class="fas fa-share-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Promo Card 2 -->
                <div class="promo-card" data-category="bundle">
                    <div class="promo-badge-top">
                        <div class="badge-new">
                            <i class="fas fa-star"></i> BARU
                        </div>
                    </div>
                    <div class="promo-image">
                        <img src="https://images.unsplash.com/photo-1581094794329-c8112a89af12?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Paket Sparepart">
                    </div>
                    <div class="promo-content">
                        <div class="promo-category">PAKET BUNDLE</div>
                        <h3 class="promo-title">Paket Lengkap Maintenance Burner</h3>
                        <p class="promo-description">Sparepart lengkap + jasa instalasi + training teknis. Hemat hingga Rp 2.500.000.</p>
                        <div class="promo-meta">
                            <div class="promo-period">
                                <i class="far fa-calendar"></i> 1 Des 2023 - 31 Jan 2024
                            </div>
                            <div class="promo-code">KODE: BURNERPAKET</div>
                        </div>
                        <div class="promo-actions">
                            <a href="produk.php?category=FBR Burner" class="btn-promo-detail">
                                <i class="fas fa-shopping-cart"></i> Beli Paket
                            </a>
                            <button class="btn-share">
                                <i class="fas fa-share-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Promo Card 3 -->
                <div class="promo-card" data-category="freeship">
                    <div class="promo-badge-top">
                        <div class="badge-hot">
                            <i class="fas fa-fire"></i> HOT
                        </div>
                    </div>
                    <div class="promo-image">
                        <img src="https://images.unsplash.com/photo-1581094794329-c8112a89af12?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Gratis Ongkir">
                    </div>
                    <div class="promo-content">
                        <div class="promo-category">GRATIS ONGKIR</div>
                        <h3 class="promo-title">Gratis Pengiriman Seluruh Indonesia</h3>
                        <p class="promo-description">Bebas biaya pengiriman untuk semua pesanan di atas Rp 5.000.000. Berlaku untuk seluruh produk.</p>
                        <div class="promo-meta">
                            <div class="promo-period">
                                <i class="far fa-calendar"></i> 1-31 Des 2023
                            </div>
                            <div class="promo-code">KODE: GRATISONGKIR</div>
                        </div>
                        <div class="promo-actions">
                            <a href="produk.php" class="btn-promo-detail">
                                <i class="fas fa-truck"></i> Belanja Sekarang
                            </a>
                            <button class="btn-share">
                                <i class="fas fa-share-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Promo Card 4 -->
                <div class="promo-card" data-category="voucher">
                    <div class="promo-badge-top">
                        <div class="badge-discount">
                            <i class="fas fa-percentage"></i> -20%
                        </div>
                    </div>
                    <div class="promo-image">
                        <img src="https://images.unsplash.com/photo-1581094794329-c8112a89af12?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Voucher Sparepart">
                    </div>
                    <div class="promo-content">
                        <div class="promo-category">VOUCHER</div>
                        <h3 class="promo-title">Voucher 20% Pembelian Sparepart</h3>
                        <p class="promo-description">Dapatkan diskon 20% untuk pembelian sparepart apa saja. Minimal pembelian Rp 3.000.000.</p>
                        <div class="promo-meta">
                            <div class="promo-period">
                                <i class="far fa-calendar"></i> 15-31 Des 2023
                            </div>
                            <div class="promo-code">KODE: SPARE20</div>
                        </div>
                        <div class="promo-actions">
                            <a href="produk.php?category=Sparepart" class="btn-promo-detail">
                                <i class="fas fa-ticket-alt"></i> Gunakan Voucher
                            </a>
                            <button class="btn-share">
                                <i class="fas fa-share-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Promo Card 5 -->
                <div class="promo-card" data-category="installment">
                    <div class="promo-badge-top">
                        <div class="badge-new">
                            <i class="fas fa-star"></i> BARU
                        </div>
                    </div>
                    <div class="promo-image">
                        <img src="https://images.unsplash.com/photo-1581094794329-c8112a89af12?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Cicilan 0%">
                    </div>
                    <div class="promo-content">
                        <div class="promo-category">CICILAN 0%</div>
                        <h3 class="promo-title">Cicilan 0% 12 Bulan BCA & Mandiri</h3>
                        <p class="promo-description">Beli produk di atas Rp 10 juta dapatkan cicilan 0% hingga 12 bulan. Syarat dan ketentuan berlaku.</p>
                        <div class="promo-meta">
                            <div class="promo-period">
                                <i class="far fa-calendar"></i> 1 Des 2023 - 28 Feb 2024
                            </div>
                            <div class="promo-code">KODE: CICIL0</div>
                        </div>
                        <div class="promo-actions">
                            <a href="produk.php?featured=1" class="btn-promo-detail">
                                <i class="fas fa-credit-card"></i> Ajukan Cicilan
                            </a>
                            <button class="btn-share">
                                <i class="fas fa-share-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Promo Card 6 -->
                <div class="promo-card" data-category="discount">
                    <div class="promo-badge-top">
                        <div class="badge-discount">
                            <i class="fas fa-percentage"></i> -25%
                        </div>
                    </div>
                    <div class="promo-image">
                        <img src="https://images.unsplash.com/photo-1581094794329-c8112a89af12?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Diskon Valve">
                    </div>
                    <div class="promo-content">
                        <div class="promo-category">DISKON PRODUK</div>
                        <h3 class="promo-title">Diskon 25% Valve & Instrumentation</h3>
                        <p class="promo-description">Promo spesial untuk semua produk valve, gauge, dan instrumentation. Limited stock!</p>
                        <div class="promo-meta">
                            <div class="promo-period">
                                <i class="far fa-calendar"></i> 10-20 Des 2023
                            </div>
                            <div class="promo-code">KODE: VALVE25</div>
                        </div>
                        <div class="promo-actions">
                            <a href="produk.php?category=Valve & Instrumentation" class="btn-promo-detail">
                                <i class="fas fa-shopping-cart"></i> Beli Sekarang
                            </a>
                            <button class="btn-share">
                                <i class="fas fa-share-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mega Promo Section -->
        <div class="mega-promo-section">
            <div class="mega-promo-card">
                <div class="mega-promo-content">
                    <div class="mega-promo-badge">
                        <i class="fas fa-crown"></i> PROMO MEGA
                    </div>
                    <h2 class="mega-promo-title">Year End Sale 2023</h2>
                    <p class="mega-promo-description">Raih kesempatan terbaik di akhir tahun! Diskon hingga 40% + gratis instalasi + garansi ekstend untuk pembelian paket lengkap.</p>
                    <ul class="mega-promo-features">
                        <li><i class="fas fa-check-circle"></i> Diskon hingga 40% untuk produk terpilih</li>
                        <li><i class="fas fa-check-circle"></i> Gratis instalasi dan training teknis</li>
                        <li><i class="fas fa-check-circle"></i> Garansi ekstend menjadi 2 tahun</li>
                        <li><i class="fas fa-check-circle"></i> Free konsultasi engineering 1 tahun</li>
                    </ul>
                    <a href="produk.php" class="mega-promo-cta">
                        <i class="fas fa-gift"></i> DAPATKAN PROMO INI
                    </a>
                </div>
                <div class="mega-promo-image">
                    <img src="https://images.unsplash.com/photo-1581094794329-c8112a89af12?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Year End Sale">
                </div>
            </div>
        </div>

        <!-- Newsletter Section -->
        <div class="newsletter-section">
            <div class="newsletter-icon">
                <i class="fas fa-envelope-open-text"></i>
            </div>
            <h2 class="newsletter-title">Dapatkan Info Promo Terbaru</h2>
            <p class="newsletter-description">Daftarkan email Anda untuk mendapatkan notifikasi promo terbaru, penawaran eksklusif, dan tips industrial langsung ke inbox Anda.</p>
            <form class="newsletter-form" id="newsletterForm">
                <input type="email" class="newsletter-input" placeholder="Masukkan email Anda" required>
                <button type="submit" class="newsletter-button">
                    <i class="fas fa-paper-plane"></i> DAFTAR
                </button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <img src="gambar/LOGO-white.png" alt="Megatek Logo" class="footer-logo">
                    <p>PT. Megatek Industrial Persada - Your trusted partner for industrial solutions since 2010.</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Products</h5>
                    <ul class="footer-links">
                        <li><a href="produk.php?category=FBR Burner">Burner Series</a></li>
                        <li><a href="produk.php?category=Boiler">Boiler Series</a></li>
                        <li><a href="produk.php?category=Valve & Instrumentation">Valve & Instrumentation</a></li>
                        <li><a href="produk.php?category=Sparepart">Spare Parts</a></li>
                        <li><a href="produk.php">All Products</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Promotions</h5>
                    <ul class="footer-links">
                        <li><a href="promo.php">All Promotions</a></li>
                        <li><a href="#flash-sale">Flash Sale</a></li>
                        <li><a href="#all-promo">Discount Vouchers</a></li>
                        <li><a href="#all-promo">Bundle Offers</a></li>
                        <li><a href="#all-promo">Installment Plans</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Contact Info</h5>
                    <p><i class="fas fa-map-marker-alt me-2"></i> Surabaya, Indonesia</p>
                    <p><i class="fas fa-phone me-2"></i> +62 31 1234 5678</p>
                    <p><i class="fas fa-envelope me-2"></i> promo@megatek.co.id</p>
                    <p><i class="fas fa-clock me-2"></i> Senin - Jumat: 8:00 - 17:00</p>
                </div>
            </div>
            <div class="copyright">
                © Copyright 2023 PT. Megatek Industrial Persada. All rights reserved.
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    
    <script>
        // Initialize Swiper
        var swiper = new Swiper(".mySwiper", {
            spaceBetween: 30,
            centeredSlides: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
            loop: true,
        });

        // Flash Sale Timer
        function updateFlashSaleTimer() {
            const timerElement = document.getElementById('flashSaleTimer');
            const now = new Date();
            const endOfDay = new Date();
            endOfDay.setHours(23, 59, 59, 999);
            
            const diff = endOfDay - now;
            
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);
            
            timerElement.textContent = 
                `${hours.toString().padStart(2, '0')}:` +
                `${minutes.toString().padStart(2, '0')}:` +
                `${seconds.toString().padStart(2, '0')}`;
        }

        // Update timer every second
        setInterval(updateFlashSaleTimer, 1000);
        updateFlashSaleTimer();

        // Promo Filtering
        const filterButtons = document.querySelectorAll('.filter-btn');
        const promoCards = document.querySelectorAll('.promo-card');

        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                filterButtons.forEach(btn => btn.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
                
                const filterValue = this.getAttribute('data-filter');
                
                // Show/hide promo cards based on filter
                promoCards.forEach(card => {
                    if (filterValue === 'all' || card.getAttribute('data-category') === filterValue) {
                        card.style.display = 'block';
                        setTimeout(() => {
                            card.style.opacity = '1';
                            card.style.transform = 'translateY(0)';
                        }, 100);
                    } else {
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(20px)';
                        setTimeout(() => {
                            card.style.display = 'none';
                        }, 300);
                    }
                });
            });
        });

        // Share button functionality
        const shareButtons = document.querySelectorAll('.btn-share');
        shareButtons.forEach(button => {
            button.addEventListener('click', function() {
                const card = this.closest('.promo-card');
                const title = card.querySelector('.promo-title').textContent;
                const description = card.querySelector('.promo-description').textContent;
                const promoCode = card.querySelector('.promo-code').textContent;
                
                const shareText = `🔥 PROMO MEGATEK 🔥\n\n${title}\n\n${description}\n\n${promoCode}\n\nCek promo lengkapnya di: ${window.location.href}`;
                
                if (navigator.share) {
                    navigator.share({
                        title: 'Promo Megatek Industrial Persada',
                        text: shareText,
                        url: window.location.href,
                    });
                } else {
                    // Fallback: Copy to clipboard
                    navigator.clipboard.writeText(shareText).then(() => {
                        alert('Promo berhasil disalin ke clipboard!');
                    });
                }
            });
        });

        // Newsletter Form
        const newsletterForm = document.getElementById('newsletterForm');
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;
            
            // Simulate subscription
            this.innerHTML = `
                <div style="text-align: center; padding: 20px;">
                    <i class="fas fa-check-circle" style="font-size: 3rem; color: #28a745;"></i>
                    <h3 style="color: var(--primary-blue); margin: 15px 0;">Terima Kasih!</h3>
                    <p>Email ${email} berhasil terdaftar untuk menerima info promo terbaru.</p>
                </div>
            `;
            
            // Reset form after 3 seconds
            setTimeout(() => {
                this.innerHTML = `
                    <input type="email" class="newsletter-input" placeholder="Masukkan email Anda" required>
                    <button type="submit" class="newsletter-button">
                        <i class="fas fa-paper-plane"></i> DAFTAR
                    </button>
                `;
                this.querySelector('input').value = '';
            }, 3000);
        });

        // Buy Now buttons for flash sale
        const buyNowButtons = document.querySelectorAll('.btn-buy-now');
        buyNowButtons.forEach(button => {
            button.addEventListener('click', function() {
                const productCard = this.closest('.flash-sale-product');
                const productName = productCard.querySelector('.product-name').textContent;
                const productPrice = productCard.querySelector('.discount-price').textContent;
                
                // Show confirmation modal
                const modal = document.createElement('div');
                modal.style.position = 'fixed';
                modal.style.top = '0';
                modal.style.left = '0';
                modal.style.width = '100%';
                modal.style.height = '100%';
                modal.style.backgroundColor = 'rgba(0,0,0,0.5)';
                modal.style.display = 'flex';
                modal.style.justifyContent = 'center';
                modal.style.alignItems = 'center';
                modal.style.zIndex = '10000';
                
                modal.innerHTML = `
                    <div style="background: white; padding: 30px; border-radius: 15px; max-width: 400px; width: 90%; text-align: center;">
                        <i class="fas fa-bolt" style="font-size: 2.5rem; color: #ff5722; margin-bottom: 15px;"></i>
                        <h3 style="color: var(--primary-blue); margin-bottom: 10px;">Flash Sale!</h3>
                        <p>Anda akan membeli:</p>
                        <p><strong>${productName}</strong></p>
                        <p><strong>${productPrice}</strong></p>
                        <div style="margin-top: 20px; display: flex; gap: 10px;">
                            <button id="confirmBuy" style="flex: 1; background: #d84315; color: white; border: none; padding: 12px; border-radius: 8px; font-weight: 600;">
                                LANJUTKAN
                            </button>
                            <button id="cancelBuy" style="flex: 1; background: #f8f9fa; color: #666; border: 1px solid #ddd; padding: 12px; border-radius: 8px;">
                                BATAL
                            </button>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(modal);
                
                modal.querySelector('#confirmBuy').addEventListener('click', function() {
                    modal.remove();
                    // Redirect to checkout or cart
                    window.location.href = 'checkout.php';
                });
                
                modal.querySelector('#cancelBuy').addEventListener('click', function() {
                    modal.remove();
                });
                
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        modal.remove();
                    }
                });
            });
        });

        // Cart count update
        function updateCartCount() {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'get_cart_count.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            document.getElementById('cartCount').textContent = response.count;
                        }
                    } catch (e) {
                        console.error('Error updating cart count:', e);
                    }
                }
            };
            xhr.send();
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
            
            // Auto-update cart count every 30 seconds
            setInterval(updateCartCount, 30000);
            
            // Search functionality
            const searchInput = document.querySelector('.search-bar input');
            const searchButton = document.querySelector('.search-bar button');
            
            searchButton.addEventListener('click', function() {
                const searchTerm = searchInput.value.trim();
                if (searchTerm !== '') {
                    window.location.href = 'produk.php?search=' + encodeURIComponent(searchTerm);
                }
            });
            
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    const searchTerm = this.value.trim();
                    if (searchTerm !== '') {
                        window.location.href = 'produk.php?search=' + encodeURIComponent(searchTerm);
                    }
                }
            });
        });
    </script>
</body>
</html>