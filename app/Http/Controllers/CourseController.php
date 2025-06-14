<?php
namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Course;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::with('modules.contents')->get();
        // dd('sammo');
        return view('course.index', compact('courses'));
    }

    public function create()
    {
        return view('course.create');
    }

    public function store(Request $request)
    {
        // Validate input data
        $request->validate([
            'title'                      => 'required|string|max:255',
            'description'                => 'required|string',
            'category'                   => 'required|string|max:100',
            'modules'                    => 'required|array|min:1',
            'modules.*.title'            => 'required|string|max:255',
            'modules.*.contents'         => 'required', 'array', 'min:1',
            'modules.*.contents.*.type'  => 'required|in:text,image,video,link',
            'modules.*.contents.*.value' => 'required|string',
            'modules.*.contents.*.file'  => 'nullable|file|mimes:jpg,png,mp4,pdf|max:20480',
        ]);

        DB::beginTransaction();
        try {
            // Create course
            $course = Course::create([
                'title'       => $request->title,
                'description' => $request->description,
                'category'    => $request->category,
            ]);

            // Create modules and contents
            foreach ($request->modules as $moduleData) {
                $module = Module::create([
                    'title'     => $moduleData['title'],
                    'course_id' => $course->id,
                ]);

                foreach ($moduleData['contents'] as $contentData) {
                    $filePath = null;
                    if (isset($contentData['file']) && $contentData['file']) {
                        $filePath = $contentData['file']->store('content_files', 'public');
                    }

                    Content::create([
                        'type'      => $contentData['type'],
                        'value'     => $contentData['value'],
                        'file_path' => $filePath,
                        'module_id' => $module->id,
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Course created successfully!',
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create course: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $course = Course::with('modules.contents')->findOrFail($id);
        return view('course.edit', compact('course'));
    }

    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        $request->validate([
            'title'                      => 'required|string|max:255',
            'description'                => 'required|string',
            'category'                   => 'required|string|max:100',
            'modules.*.title'            => 'required|string|max:255',
            'modules.*.contents.*.type'  => 'required|in:text,image,video,link',
            'modules.*.contents.*.value' => 'required|string',
            'modules.*.contents.*.file'  => 'nullable|file|mimes:jpg,png,mp4,pdf|max:20480',
        ]);

        DB::beginTransaction();
        try {
            $course->update([
                'title'       => $request->title,
                'description' => $request->description,
                'category'    => $request->category,
            ]);

            // Delete existing modules and contents
            foreach ($course->modules as $module) {
                foreach ($module->contents as $content) {
                    if ($content->file_path) {
                        Storage::disk('public')->delete($content->file_path);
                    }
                    $content->delete();
                }
                $module->delete();
            }

            // Create new modules and contents
            foreach ($request->modules as $moduleData) {
                $module = Module::create([
                    'title'     => $moduleData['title'],
                    'course_id' => $course->id,
                ]);

                foreach ($moduleData['contents'] as $contentData) {
                    $filePath = null;
                    if (isset($contentData['file']) && $contentData['file']) {
                        $filePath = $contentData['file']->store('content_files', 'public');
                    }

                    Content::create([
                        'type'      => $contentData['type'],
                        'value'     => $contentData['value'],
                        'file_path' => $filePath,
                        'module_id' => $module->id,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('courses.index')->with('success', 'Course updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update course: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $course = Course::findOrFail($id);

        foreach ($course->modules as $module) {
            foreach ($module->contents as $content) {
                if ($content->file_path) {
                    Storage::disk('public')->delete($content->file_path);
                }
            }
        }

        $course->delete();
        return back()->with('success', 'Course deleted successfully!');
    }
}
