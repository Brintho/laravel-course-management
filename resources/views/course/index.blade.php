@extends('layout.app')

@section('title', 'Courses List')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Courses List</h4>
            <a href="{{ route('courses.create') }}" class="btn btn-primary">Add Course</a>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Modules</th>
                            <th>Files</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($courses as $index => $course)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $course->title }}</td>
                                <td>{{ $course->category }}</td>
                                <td>{{ $course->modules->count() }}</td>
                                <td>
                                    @if ($course->modules->isNotEmpty())
                                        @php
                                            $files = $course->modules->flatMap(function ($module) {
                                                return $module->contents->where('file_path', '!=', null);
                                            });
                                        @endphp
                                        @if ($files->isNotEmpty())
                                            @foreach ($files as $content)
                                                <div>
                                                    <a href="{{ asset('storage/' . $content->file_path) }}" target="_blank"
                                                        class="btn btn-sm btn-info">View File</a>
                                                </div>
                                            @endforeach
                                        @else
                                            <span class="text-muted">No files uploaded</span>
                                        @endif
                                    @else
                                        <span class="text-muted">No files uploaded</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('courses.edit', $course->id) }}"
                                        class="btn btn-sm btn-primary">Edit</a>
                                    <form action="{{ route('courses.destroy', $course->id) }}" method="POST"
                                        style="display:inline;"
                                        onsubmit="return confirm('Are you sure you want to delete this course?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No courses found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
