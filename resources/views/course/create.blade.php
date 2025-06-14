@extends('layout.app')

@section('title', 'Create Course')

@section('content')
    <div class="card">
        <div class="card-header">
            <h4>Create New Course</h4>
        </div>
        <div class="card-body">
            {{-- Show success message --}}
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Show error messages --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="courseForm" action="{{ route('courses.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="title" class="form-label">Course Title</label>
                    <input type="text" id="title" name="title" class="form-control" value="{{ old('title') }}"
                        required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="4" required>{{ old('description') }}</textarea>
                </div>
                <div class="mb-3">
                    <label for="category" class="form-label">Category</label>
                    <input type="text" id="category" name="category" class="form-control" value="{{ old('category') }}"
                        required>
                </div>

                <div id="modulesContainer" class="mb-3"></div>
                <button type="button" id="addModule" class="btn btn-primary ">Add Module</button>
                <button type="submit" class="btn btn-success">Create Course</button>
            </form>
        </div>
    </div>

    <!-- Bootstrap Toast Container -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1050;">
        <!-- Success Toast -->
        <div id="successToast" class="toast align-items-center text-white bg-success border-0" role="alert"
            aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <span id="successToastMessage"></span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                    aria-label="Close"></button>
            </div>
        </div>
        <!-- Error Toast -->
        <div id="errorToast" class="toast align-items-center text-white bg-danger border-0" role="alert"
            aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <span id="errorToastMessage"></span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                    aria-label="Close"></button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            let moduleCount = 0;

            // Initialize Bootstrap Toasts
            const successToastElement = document.getElementById('successToast');
            const successToast = new bootstrap.Toast(successToastElement);
            const errorToastElement = document.getElementById('errorToast');
            const errorToast = new bootstrap.Toast(errorToastElement);

            // Add new module
            $('#addModule').click(function() {
                moduleCount++;
                const moduleHtml = `
            <div class="module-card bg-light p-3 rounded mb-3" data-module-id="${moduleCount}">
                <div class="d-flex justify-content-between mb-3">
                    <h5>Module ${moduleCount}</h5>
                    <button type="button" class="btn btn-danger btn-sm removeModule">Remove</button>
                </div>
                <div class="mb-3">
                    <label class="form-label">Module Title</label>
                    <input type="text" name="modules[${moduleCount}][title]" class="form-control module-title" required>
                </div>
                <div class="contents-container mb-3"></div>
                <button type="button" class="btn btn-primary btn-sm addContent">Add Content</button>
            </div>
        `;
                $('#modulesContainer').append(moduleHtml);
            });

            // Add new content
            $(document).on('click', '.addContent', function() {
                const moduleCard = $(this).closest('.module-card');
                const moduleId = moduleCard.data('module-id');
                const contentCount = moduleCard.find('.content-card').length + 1;

                const contentHtml = `
            <div class="content-card bg-white p-2 rounded mb-2">
                <div class="d-flex justify-content-between mb-2">
                    <label class="form-label">Content ${contentCount}</label>
                    <button type="button" class="btn btn-danger btn-sm removeContent">Remove</button>
                </div>
                <div class="mb-3">
                    <label class="form-label">Content Type</label>
                    <select name="modules[${moduleId}][contents][${contentCount}][type]" class="form-control content-type" required>
                        <option value="text">Text</option>
                        <option value="image">Image</option>
                        <option value="video">Video</option>
                        <option value="link">Link</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Content Value</label>
                    <textarea name="modules[${moduleId}][contents][${contentCount}][value]" class="form-control content-value" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Upload File (optional)</label>
                    <input type="file" name="modules[${moduleId}][contents][${contentCount}][file]" class="form-control">
                </div>
            </div>
        `;
                moduleCard.find('.contents-container').append(contentHtml);
            });

            // Remove module
            $(document).on('click', '.removeModule', function() {
                $(this).closest('.module-card').remove();
            });

            // Remove content
            $(document).on('click', '.removeContent', function() {
                $(this).closest('.content-card').remove();
            });

            // Handle form submission with AJAX
            $('#courseForm').submit(function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                $.ajax({
                    url: '{{ route('courses.store') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        console.log('Success Response:', response); // Debug response
                        // Show success toast
                        $('#successToastMessage').text(response.message ||
                            'Course created successfully!');
                        successToast.show();
                        $('#courseForm')[0].reset();
                        $('#modulesContainer').empty();
                    },
                    error: function(xhr) {
                        console.log('Error Response:', xhr.responseJSON); // Debug error
                        let message = 'An error occurred.';
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            message = Object.values(errors).flat().join('\n');
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        // Show error toast
                        $('#errorToastMessage').text(message);
                        errorToast.show();
                    }
                });
            });
        });
    </script>
@endsection
