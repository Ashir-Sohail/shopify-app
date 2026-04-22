@extends('layouts.app')
@section('content')
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #f6f6f7;
            padding: 40px 20px;
        }

        .card-box {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            padding: 35px;
            max-width: 750px;
            margin: auto;
        }

        .status-badge {
            background: #e4f1eb;
            color: #008060;
            padding: 6px 12px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 700;
        }

        .shop-name {
            color: #0d6efd;
            font-weight: 600;
        }

        .btn-custom {
            padding: 12px 22px;
            border-radius: 10px;
            font-weight: 600;
        }
    </style>
    </head>



    <div class="card-box">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0">Welcome to Devnest Connector</h2>

            <span class="status-badge">
                Connected
            </span>
        </div>

        <p class="text-muted">
            You are currently viewing the dashboard for:
            <strong class="shop-name">{{ $shop }}</strong>
        </p>

        <hr>

        <p class="text-secondary mb-4">
            Manage your Shopify store products directly from this dashboard.
        </p>

        <!-- Buttons -->
        <div class="d-flex gap-2">

            <!-- View Products -->
            <a href="{{ route('shopify.products', ['shop' => $shop]) }}" class="btn btn-primary btn-custom">
                View Products
            </a>

            <!-- Open Popup -->
            <button class="btn btn-success btn-custom" data-bs-toggle="modal" data-bs-target="#productModal">
                Add Product
            </button>

        </div>

    </div>

    <!-- Modal Popup -->
    <div class="modal fade" id="productModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form action="{{ route('shopify.product.store') }}" method="POST">
                    @csrf

                    <div class="modal-body">

                        <input type="hidden" name="shop" value="{{ $shop }}">

                        <div class="mb-3">
                            <label class="form-label">Product Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Price</label>
                            <input type="text" name="price" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4"></textarea>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Close
                        </button>

                        <button type="submit" class="btn btn-success">
                            Save Product
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
@endsection
