<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Test;
use App\Models\Subject;
use App\Models\ClassLevel;
use App\Models\Question;

new class extends Component {
    use WithPagination;

    // Properties
    public $test_id;
    public $title;
    public $class_level_id;
    public $subject_id;
    public $duration = 30;
    public $is_published = false;

    public $selectedQuestions = [];
    public $availableQuestions = [];

    // Search / Pagination / Sorting
    public $search = '';
    public $perPage = 10;
    public $sortField = 'id';
    public $sortDirection = 'desc';
    public $showTrashed = false;
    public $activeTab = 'index';

    // Rules
    protected function rules()
    {
        return [
            'title' => 'required|string|min:3|max:255',
            'class_level_id' => 'required|exists:class_levels,id',
            'subject_id' => 'required|exists:subjects,id',
            'duration' => 'required|integer|min:1',
            'is_published' => 'boolean',
        ];
    }

    // Tabs
    public function showTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset(['test_id', 'title', 'class_level_id', 'subject_id', 'duration', 'is_published', 'selectedQuestions', 'availableQuestions']);
        $this->resetErrorBag();
    }

    // Edit
    public function editTest($id)
    {
        $test = Test::with('questions')->findOrFail($id);

        $this->test_id = $test->id;
        $this->title = $test->title;
        $this->class_level_id = $test->class_level_id;
        $this->subject_id = $test->subject_id;
        $this->duration = $test->duration;
        $this->is_published = $test->is_published;
        $this->selectedQuestions = $test->questions->pluck('id')->toArray();

        $this->loadAvailableQuestions();
        $this->activeTab = 'edit';
    }

    // Save
    public function saveTest()
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'class_level_id' => $this->class_level_id,
            'subject_id' => $this->subject_id,
            'duration' => $this->duration,
            'is_published' => $this->is_published,
            'total_questions' => count($this->selectedQuestions),
            'total_marks' => $this->calculateTotalMarks(),
        ];

        if ($this->test_id) {
            Test::findOrFail($this->test_id)->update($data);
            Test::find($this->test_id)->questions()->sync($this->selectedQuestions);
            session()->flash('success', 'Test updated successfully.');
        } else {
            $test = Test::create($data);
            $test->questions()->attach($this->selectedQuestions);
            session()->flash('success', 'Test created successfully.');
        }

        $this->resetForm();
        $this->activeTab = 'index';
    }

    public function calculateTotalMarks()
    {
        return Question::whereIn('id', $this->selectedQuestions)->sum('marks');
    }

    // Delete / Restore / Force Delete
    public function deleteTest($id)
    {
        Test::findOrFail($id)->delete();
        session()->flash('success', 'Test moved to trash.');
    }

    public function restoreTest($id)
    {
        Test::withTrashed()->findOrFail($id)->restore();
        session()->flash('success', 'Test restored successfully.');
    }

    public function forceDeleteTest($id)
    {
        Test::withTrashed()->findOrFail($id)->forceDelete();
        session()->flash('success', 'Test permanently deleted.');
    }

    // Load available questions by subject
    public function updatedSubjectId()
    {
        $this->loadAvailableQuestions();
        $this->selectedQuestions = [];
    }

    public function loadAvailableQuestions()
    {
        if ($this->subject_id) {
            $this->availableQuestions = Question::where('subject_id', $this->subject_id)->get()->toArray();
        } else {
            $this->availableQuestions = [];
        }
    }

    // Add / Remove Questions
    public function addQuestion($id)
    {
        if (!in_array($id, $this->selectedQuestions)) {
            $this->selectedQuestions[] = $id;
        }
    }

    public function removeQuestion($index)
    {
        unset($this->selectedQuestions[$index]);
        $this->selectedQuestions = array_values($this->selectedQuestions);
    }

    // Sort
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    // Data
    public function getTestsProperty()
    {
        $query = Test::with(['classLevel', 'subject']);
        if ($this->showTrashed) {
            $query->onlyTrashed();
        }

        return $query->when($this->search, fn($q) => $q->where('title', 'like', '%' . $this->search . '%')->orWhereHas('subject', fn($sub) => $sub->where('name', 'like', '%' . $this->search . '%')))->orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage);
    }

    public function getClassLevelsProperty()
    {
        return ClassLevel::where('is_active', true)->orderBy('name')->get();
    }

    public function getSubjectsProperty()
    {
        return Subject::where('is_active', true)->when($this->class_level_id, fn($q) => $q->where('class_level_id', $this->class_level_id))->orderBy('name')->get();
    }
};
?>
<section class="space-y-6">
    <div class="flex flex-col space-y-6">

        {{-- Create/Edit Form --}}
        @if ($activeTab === 'create' || $activeTab === 'edit')
            <div>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold">{{ $activeTab === 'create' ? 'Create New Test' : 'Edit Test' }}
                    </h3>
                    <flux:button wire:click="showTab('index')" size="sm">Back</flux:button>
                </div>

                <form wire:submit="saveTest" class="grid grid-cols-1 gap-6">
                    <flux:select wire:model.live="class_level_id" label="Class Level">
                        <option value="">Select Class Level</option>
                        @foreach ($this->classLevels as $level)
                            <option value="{{ $level->id }}">{{ $level->name }}</option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model.live="subject_id" label="Subject">
                        <option value="">Select Subject</option>
                        @foreach ($this->subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </flux:select>

                    <flux:input wire:model="title" label="Test Title" />
                    <flux:input type="number" wire:model="duration" label="Duration (minutes)" />
                    <flux:checkbox wire:model="is_published" label="Publish Test" />

                    @if ($subject_id)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Available Questions --}}
                            <div class="border rounded p-4 max-h-72 overflow-y-auto">
                                <h5 class="font-semibold mb-2">Available Questions ({{ count($availableQuestions) }})
                                </h5>
                                @foreach ($availableQuestions as $q)
                                    <div wire:click="addQuestion({{ $q['id'] }})"
                                        class="p-2 mb-2 border rounded cursor-pointer hover:bg-gray-100">
                                        {{ \Illuminate\Support\Str::limit($q['question_text'], 80) }} <small>Marks:
                                            {{ $q['marks'] }}</small>
                                    </div>
                                @endforeach
                            </div>
                            {{-- Selected Questions --}}
                            <div class="border rounded p-4 max-h-72 overflow-y-auto">
                                <h5 class="font-semibold mb-2">Selected Questions ({{ count($selectedQuestions) }})</h5>
                                @forelse($selectedQuestions as $i=>$id)
                                    @php $q = collect($availableQuestions)->firstWhere('id',$id); @endphp
                                    <div class="p-2 mb-2 border rounded bg-gray-100 flex justify-between items-center">
                                        <div>{{ \Illuminate\Support\Str::limit($q['question_text'], 80) }}
                                            <small>Marks:
                                                {{ $q['marks'] }}</small>
                                        </div>
                                        <button wire:click="removeQuestion({{ $i }})"
                                            class="text-red-600 font-bold">&times;</button>
                                    </div>
                                @empty
                                    <p class="text-gray-500">No questions selected</p>
                                @endforelse
                            </div>
                        </div>
                    @endif

                    <div class="mt-6 flex justify-end space-x-3">
                        <flux:button type="button" wire:click="showTab('index')">Cancel</flux:button>
                        <flux:button type="submit">{{ $activeTab === 'create' ? 'Create' : 'Update' }}</flux:button>
                    </div>
                </form>
            </div>
        @else
            {{-- Header --}}
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                <h2 class="text-2xl font-bold">Test Management</h2>
                <div class="flex space-x-2">
                    <flux:button wire:click="showTab('create')">Create New</flux:button>
                    <flux:button wire:click="$set('showTrashed', false)"
                        variant="{{ !$showTrashed ? 'primary' : 'filled' }}">Active</flux:button>
                    <flux:button wire:click="$set('showTrashed', true)"
                        variant="{{ $showTrashed ? 'primary' : 'filled' }}">Trashed</flux:button>
                </div>
            </div>

            {{-- Search --}}
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search tests..." />

            {{-- Table --}}
            <div class="overflow-x-auto rounded-lg shadow">
                <table class="min-w-full divide-y divide-gray-400/25">
                    <thead>
                        <tr>
                            <th wire:click="sortBy('id')"
                                class="px-6 py-3 text-left text-xs font-medium uppercase cursor-pointer">
                                ID {!! $sortField === 'id' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' !!}
                            </th>
                            <th wire:click="sortBy('title')"
                                class="px-6 py-3 text-left text-xs font-medium uppercase cursor-pointer">
                                Title {!! $sortField === 'title' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' !!}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase">Class / Subject</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase">Questions / Marks</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase">Duration</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase">Published</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-400/25">
                        @forelse($this->tests as $t)
                            <tr class="hover:bg-zinc-400/10">
                                <td class="px-6 py-4">{{ $t->id }}</td>
                                <td class="px-6 py-4 truncate max-w-xs">{{ $t->title }}</td>
                                <td class="px-6 py-4">{{ $t->classLevel->name ?? '-' }} /
                                    {{ $t->subject->name ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $t->total_questions }} / {{ $t->total_marks }}</td>
                                <td class="px-6 py-4">{{ $t->duration }} mins</td>
                                <td class="px-6 py-4">{{ $t->is_published ? 'Yes' : 'No' }}</td>
                                <td class="px-6 py-4 text-right">
                                    @if ($t->deleted_at)
                                        <flux:link as="button" wire:click="restoreTest({{ $t->id }})"
                                            variant="ghost" size="sm" class="text-green-600">
                                            Restore</flux:link>
                                        <flux:link as="button" wire:click="forceDeleteTest({{ $t->id }})"
                                            variant="ghost" size="sm"
                                            wire:confirm="Are you sure you want to delete this test?"
                                            class="text-red-600">
                                            Delete Permanently</flux:link>
                                    @else
                                        <flux:link as="button" wire:click="editTest({{ $t->id }})"
                                            variant="ghost" class="text-blue-600" size="sm">Edit
                                        </flux:link>
                                        <flux:link as="button" wire:click="deleteTest({{ $t->id }})"
                                            variant="ghost" wire:confirm="Are you sure you want to delete this test?"
                                            class="text-yellow-600">
                                            Delete</flux:link>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center">No
                                    {{ $showTrashed ? 'trashed' : 'active' }} tests found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Pagination --}}
                @if ($this->tests->hasPages())
                    <div class="px-6 py-3 border-t">{{ $this->tests->links() }}</div>
                @endif
            </div>
        @endif
    </div>
</section>
