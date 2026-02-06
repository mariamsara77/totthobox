<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Question;
use App\Models\Subject;
use App\Models\ClassLevel;

new class extends Component {
    use WithPagination;

    // Properties
    public $questionId;
    public $class_level_id;
    public $subject_id;
    public $question_text;
    public $option_a;
    public $option_b;
    public $option_c;
    public $option_d;
    public $correct_answer;
    public $marks = 1;
    public $difficulty_level = 1;
    public $explanation;
    public $is_active = true;

    public $meta_title;
    public $meta_description;
    public $meta_keywords;
    public $is_featured = false;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'id';
    public $sortDirection = 'desc';
    public $showTrashed = false;
    public $activeTab = 'index';

    // Validation Rules
    protected function rules()
    {
        return [
            'class_level_id' => 'required|exists:class_levels,id',
            'subject_id' => 'required|exists:subjects,id',
            'question_text' => 'required|string',
            'option_a' => 'required|string',
            'option_b' => 'required|string',
            'option_c' => 'required|string',
            'option_d' => 'required|string',
            'correct_answer' => 'required|in:a,b,c,d',
            'marks' => 'required|integer|min:1',
            'difficulty_level' => 'required|integer|min:1|max:5',
            'explanation' => 'nullable|string',
            'is_active' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
            'is_featured' => 'boolean',
        ];
    }

    // Tabs
    public function showTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetFields();
    }

    public function resetFields()
    {
        $this->reset([
            'questionId','class_level_id','subject_id','question_text',
            'option_a','option_b','option_c','option_d','correct_answer',
            'marks','difficulty_level','explanation',
            'meta_title','meta_description','meta_keywords',
            'is_featured','is_active'
        ]);
        $this->is_active = true;
        $this->is_featured = false;
        $this->resetErrorBag();
    }

    // Edit
    public function editQuestion($id)
    {
        $question = Question::withTrashed()->findOrFail($id);

        $this->questionId = $question->id;
        $this->class_level_id = $question->class_level_id;
        $this->subject_id = $question->subject_id;
        $this->question_text = $question->question_text;
        $this->option_a = $question->option_a;
        $this->option_b = $question->option_b;
        $this->option_c = $question->option_c;
        $this->option_d = $question->option_d;
        $this->correct_answer = $question->correct_answer;
        $this->marks = $question->marks;
        $this->difficulty_level = $question->difficulty_level;
        $this->explanation = $question->explanation;
        $this->is_active = $question->is_active;
        $this->meta_title = $question->meta_title;
        $this->meta_description = $question->meta_description;
        $this->meta_keywords = $question->meta_keywords;
        $this->is_featured = $question->is_featured;

        $this->activeTab = 'edit';
    }

    // Save
    public function saveQuestion()
    {
        $this->validate();

        $data = [
            'class_level_id' => $this->class_level_id,
            'subject_id' => $this->subject_id,
            'question_text' => $this->question_text,
            'option_a' => $this->option_a,
            'option_b' => $this->option_b,
            'option_c' => $this->option_c,
            'option_d' => $this->option_d,
            'correct_answer' => $this->correct_answer,
            'marks' => $this->marks,
            'difficulty_level' => $this->difficulty_level,
            'explanation' => $this->explanation,
            'is_active' => $this->is_active,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'is_featured' => $this->is_featured,
            'user_id' => Auth::id(),
        ];

        if ($this->questionId) {
            $data['updated_by'] = Auth::id();
            Question::findOrFail($this->questionId)->update($data);
            session()->flash('success','Question updated successfully.');
        } else {
            $data['created_by'] = Auth::id();
            Question::create($data);
            session()->flash('success','Question created successfully.');
        }

        $this->resetFields();
        $this->activeTab = 'index';
    }

    // Delete / Restore / Force Delete
    public function deleteQuestion($id)
    {
        $question = Question::findOrFail($id);
        $question->update(['deleted_by'=>Auth::id()]);
        $question->delete();
        session()->flash('success','Question moved to trash.');
    }

    public function restoreQuestion($id)
    {
        $question = Question::withTrashed()->findOrFail($id);
        $question->restore();
        session()->flash('success','Question restored successfully.');
    }

    public function forceDeleteQuestion($id)
    {
        $question = Question::withTrashed()->findOrFail($id);
        $question->forceDelete();
        session()->flash('success','Question permanently deleted.');
    }

    // Sort
    public function sortBy($field)
    {
        if($this->sortField === $field){
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc':'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    // Data
    public function getQuestionsProperty()
    {
        $query = Question::with(['classLevel','subject']);

        if($this->showTrashed){
            $query->onlyTrashed();
        }

        return $query->when($this->search,function($q){
            $q->where('question_text','like','%'.$this->search.'%')
              ->orWhereHas('subject',fn($sub)=>$sub->where('name','like','%'.$this->search.'%'));
        })->orderBy($this->sortField,$this->sortDirection)
          ->paginate($this->perPage);
    }

    public function getClassLevelsProperty()
    {
        return ClassLevel::where('is_active',true)->orderBy('name')->get();
    }

    public function getSubjectsProperty()
    {
        return Subject::where('is_active',true)
                      ->when($this->class_level_id,fn($q)=>$q->where('class_level_id',$this->class_level_id))
                      ->orderBy('name')->get();
    }
};
?>


<section class="">
    <div class="flex flex-col space-y-6">

        {{-- Create/Edit Form --}}
        @if($activeTab==='create'||$activeTab==='edit')
        <div>
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold">
                    {{ $activeTab==='create'?'Create New Question':'Edit Question' }}
                </h3>
                <flux:button wire:click="showTab('index')" size="sm">Back</flux:button>
            </div>

            <form wire:submit="saveQuestion" class="grid grid-cols-1 gap-6">
                <flux:select wire:model.live="class_level_id" label="Class Level">
                    <option value="">Select Class Level</option>
                    @foreach($this->classLevels as $level)
                    <option value="{{ $level->id }}">{{ $level->name }}</option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="subject_id" label="Subject">
                    <option value="">Select Subject</option>
                    @foreach($this->subjects as $subject)
                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </flux:select>

                <flux:textarea wire:model="question_text" label="Question Text" />

                <flux:input wire:model="option_a" label="Option A" />
                <flux:input wire:model="option_b" label="Option B" />
                <flux:input wire:model="option_c" label="Option C" />
                <flux:input wire:model="option_d" label="Option D" />

                <flux:select wire:model="correct_answer" label="Correct Answer">
                    <option value="">Select</option>
                    <option value="a">A</option>
                    <option value="b">B</option>
                    <option value="c">C</option>
                    <option value="d">D</option>
                </flux:select>

                <flux:input type="number" wire:model="marks" label="Marks" />
                <flux:input type="number" wire:model="difficulty_level" label="Difficulty Level (1-5)" />
                <flux:textarea wire:model="explanation" label="Explanation" />

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <flux:checkbox wire:model="is_active" label="Active" />
                    <flux:checkbox wire:model="is_featured" label="Featured" />
                </div>

                {{-- SEO Info --}}
                <div>
                    <h4 class="text-lg font-medium">SEO Information</h4>
                    <flux:input wire:model="meta_title" label="Meta Title" />
                    <flux:textarea wire:model="meta_description" label="Meta Description" />
                    <flux:input wire:model="meta_keywords" label="Meta Keywords" />
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <flux:button type="button" wire:click="showTab('index')">Cancel</flux:button>
                    <flux:button type="submit">{{ $activeTab==='create'?'Create':'Update' }}</flux:button>
                </div>
            </form>
        </div>
        @else

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
            <h2 class="text-2xl font-bold">Question Management</h2>
            <div class="flex space-x-2">
                <flux:button wire:click="showTab('create')">Create New</flux:button>
                <flux:button wire:click="$set('showTrashed', false)" variant="{{ !$showTrashed?'primary':'filled' }}">Active</flux:button>
                <flux:button wire:click="$set('showTrashed', true)" variant="{{ $showTrashed?'primary':'filled' }}">Trashed</flux:button>
            </div>
        </div>

        {{-- Search --}}
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search by question or subject..." />

        {{-- Table --}}
        <div class="overflow-x-auto rounded-lg shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase cursor-pointer" wire:click="sortBy('id')">
                            ID {!! $sortField==='id'?($sortDirection==='asc'?'↑':'↓'):'' !!}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">Question</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">Correct</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">Active</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($this->questions as $q)
                    <tr>
                        <td class="px-6 py-4">{{ $q->id }}</td>
                        <td class="px-6 py-4 truncate max-w-xs">{{ Str::limit($q->question_text,50) }}</td>
                        <td class="px-6 py-4">{{ $q->subject->name ?? '-' }}</td>
                        <td class="px-6 py-4 uppercase">{{ $q->correct_answer }}</td>
                        <td class="px-6 py-4">{{ $q->is_active?'Yes':'No' }}</td>
                        <td class="px-6 py-4 text-right">
                            @if($q->deleted_at)
                            <button wire:click="restoreQuestion({{ $q->id }})" class="text-green-600 mr-2">Restore</button>
                            <button wire:click="forceDeleteQuestion({{ $q->id }})" class="text-red-600" onclick="return confirm('Permanently delete?')">Delete Permanently</button>
                            @else
                            <button wire:click="editQuestion({{ $q->id }})" class="text-blue-600 mr-2">Edit</button>
                            <button wire:click="deleteQuestion({{ $q->id }})" class="text-red-600" onclick="return confirm('Move to trash?')">Delete</button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center">No {{ $showTrashed?'trashed':'active' }} questions found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination --}}
            @if($this->questions->hasPages())
            <div class="px-6 py-3 border-t">
                {{ $this->questions->links() }}
            </div>
            @endif
        </div>
        @endif
    </div>
</section>
