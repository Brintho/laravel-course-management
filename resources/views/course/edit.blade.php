@extends('layout.app')

@section('title', 'Edit Course')

@section('content')
    <div class="card">
        <div class="card-header">
            <h4>Edit Course</h4>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="courseForm" action="{{ route('courses.update', $course->id) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="title" class="form-label">Course Title</label>
                    <input type="text" id="title" name="title" class="form-control"
                        value="{{ old('title', $course->title) }}" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="4" required>{{ old('description', $course->description) }}</textarea>
                </div>
                <div class="mb-3">
                    <label for="category" class="form-label">Category</label>
                    <input type="text" id="category" name="category" class="form-control"
                        value="{{ old('category', $course->category) }}" required>
                </div>

                <div id="modulesContainer" class="mb-3">
                    @foreach ($course->modules as $mIndex => $module)
                        <div class="module-card bg-light p-3 rounded mb-3" data-module-id="{{ $mIndex + 1 }}">
                            <div class="d-flex justify-content-between mb-2">
                                <h5>Module {{ $mIndex + 1 }}</h5>
                                <button type="button" class="btn btn-danger btn-sm removeModule">Remove</button>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Module Title</label>
                                <input type="text" name="modules[{{ $mIndex + 1 }}][title]"
                                    class="form-control module-title" value="{{ $module->title }}" required>
                            </div>
                            <div class="contents-container mb-3">
                                @foreach ($module->contents as $cIndex => $content)
                                    <div class="content-card bg-white p-2 rounded mb-2">
                                        <div class="d-flex justify-content-between mb-2">
                                            <label class="form-label">Content {{ $cIndex + 1 }}</label>
                                            <button type="button"
                                                class="btn btn-danger btn-sm removeContent">Remove</button>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Content Type</label>
                                            <select
                                                name="modules[{{ $mIndex + 1 }}][contents][{{ $cIndex + 1 }}][type]"
                                                class="form-control content-type" required>
                                                <option value="text" {{ $content->type == 'text' ? 'selected' : '' }}>
                                                    Text</option>
                                                <option value="image" {{ $content->type == 'image' ? 'selected' : '' }}>
                                                    Image</option>
                                                <option value="video" {{ $content->type == 'video' ? 'selected' : '' }}>
                                                    Video</option>
                                                <option value="link" {{ $content->type == 'link' ? 'selected' : '' }}>
                                                    Link</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Content Value</label>
                                            <textarea name="modules[${mIndex + 1}][contents][${cIndex + 1}][value]" class="form-control content-value"
                                                rows="3" required>{{ $content->value }}</textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Current File (if any)</label>
                                            @if ($content->file_path)
                                                <p><a href="{{ asset('storage/' . $content->file_path) }}"
                                                        target="_blank">View File</a></p>
                                            @else
                                                <p>No file uploaded</p>
                                            @endif
                                            <label class="form-label">Upload New File (optional)</label>
                                            <input type="file"
                                                name="modules[${mIndex + 1}][contents][${cIndex + 1}][file]"
                                                class="form-control">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-primary btn-sm addContent">Add Content</button>
                        </div>
                    @endforeach
                </div>
                <button type="button" id="addModule" class="btn btn-primary mb-3">Add Module</button>
                <button type="submit" class="btn btn-success">Update Course</button>
            </form>
        </div>
    </div>

    <div class="modal fade" id="responseModal" tabindex="-1" aria-labelledby="responseModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="responseModalLabel">Notification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="modalMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            let moduleCount = {{ count($course->modules) }};

            // Add new module
            $('#addModule').on('click', function() {
                moduleCount++;
                const moduleHtml = `
            <div class="module-card bg-light p-3 rounded mb-3" data-module-id="${moduleCount}">
                <div class="d-flex justify-content-between mb-2">
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
                    <label class="form-label">Upload New File (optional)</label>
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
            $('#courseForm').on('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                $.ajax({
                    url: '{{ route('courses.update', $course->id) }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#modalMessage').text(response.message);
                        $('#responseModal').modal('show');
                        if (response.success) {
                            $('#courseForm')[0].reset();
                            $('#modulesContainer').empty();
                        }
                    },
                    error: function(xhr) {
                        let message = 'An error occurred.';
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            message = Object.values(errors).flat().join(', ');
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        $('#modalMessage').text(message);
                        $('#responseModal').modal('show');
                    }
                });
            });

            // Reset form on modal close if success
            $('#responseModal').on('hidden.bs.modal', function() {
                if ($('#modalMessage').text().includes('successfully')) {
                    $('#courseForm')[0].reset();
                    $('#modulesContainer').empty();
                }
            });
        });
    </script>
@endsection
