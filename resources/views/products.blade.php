@extends('layouts.app')
@section('content')
    <style>
        /* ... your existing styles ... */
        body {
            background: linear-gradient(135deg, #eef2ff, #f8f9fa);
            font-family: Arial, sans-serif;
        }

        .main-card {
            border-radius: 18px;
            overflow: hidden;
            border: none;
        }

        .header-section {
            background: linear-gradient(135deg, #0d6efd, #6610f2);
            padding: 25px;
        }

        .product-card {
            border: none;
            border-radius: 16px;
            transition: 0.3s ease;
            overflow: hidden;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 18px 35px rgba(0, 0, 0, 0.12);
        }

        .icon-box {
            width: 55px;
            height: 55px;
            border-radius: 14px;
            background: rgba(13, 110, 253, 0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #0d6efd;
        }

        .product-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .badge-custom {
            background: #e9f7ef;
            color: #198754;
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 12px;
        }
    </style>
    </head>

    <body>

        <div class="container py-5">
            <div class="card shadow-lg main-card">

                <div class="header-section text-white">
                    <div class="top-bar d-flex justify-content-between align-items-center">
                        <div>
                            <h2><i class="bi bi-shop me-2"></i>Shopify Products</h2>
                            <small class="opacity-75">Store: {{ $shop }}</small>
                        </div>

                        <span class="badge bg-light text-dark px-3 py-2 rounded-pill">
                            Total: {{ count($products['edges'] ?? []) }}
                        </span>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="row g-4">

                        @forelse($products['edges'] as $item)
                            <div class="col-md-6 col-lg-4">
                                <div class="card shadow-sm product-card h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="icon-box">
                                                <i class="bi bi-bag"></i>
                                            </div>
                                            <span class="badge-custom">
                                                {{ $item['node']['status'] }}
                                            </span>
                                        </div>

                                        <h5 class="product-title text-truncate">
                                            {{ $item['node']['title'] }}
                                        </h5>

                                        <p class="text-muted small mb-4">
                                            Created:
                                            {{ \Carbon\Carbon::parse($item['node']['createdAt'])->format('M d, Y') }}
                                        </p>

                                        <div class="d-grid">
                                            <a href="https://{{ $shop }}/admin/products/{{ last(explode('/', $item['node']['id'])) }}"
                                                target="_blank" class="btn btn-outline-primary rounded-pill">
                                                View in Shopify
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-5">
                                <div class="alert alert-info">
                                    No products found for this shop.
                                </div>
                            </div>
                        @endforelse

                    </div>
                </div>

            </div>
        </div>
    @endsection
