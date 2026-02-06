<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\TestQuestion;
use App\Models\Test;
use App\Models\Question;

new class extends Component {
    use WithPagination;

    public $test_question_id;
    public $test_id;
    public $question_id;
    public $order = 0;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'id';
    public $sortDirection = 'desc';
    public $activeTab = 'index';

    protected function rules()
    {
        return [
            'test_id' => 'required|exists:tests,id',
            'question_id' => 'required|exists:questions,id',
            'order' => 'integer|min:0',
        ];
    }

    public function showTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetFields();
    }

    public function resetFields()
    {
        $this->reset(['test_question_id', 'test_id', 'question_id', 'order']);
        $this->resetErrorBag();
    }

    public function edit($id)
    {
        $tq = TestQuestion::findOrFail($id);
        $this->test_question_id = $tq->id;
        $this->test_id = $tq->test_id;
        $this->question_id = $tq->question_id;
        $this->order = $tq->order;
        $this->activeTab = 'edit';
    }

    public function save()
    {
        $this->validate();

        $data = [
            'test_id' => $this->test_id,
            'question_id' => $this->question_id,
            'order' => $this->order,
        ];

        if ($this->test_question_id) {
            // Update existing record
            TestQuestion::findOrFail($this->test_question_id)->update($data);
            session()->flash('success', 'Test Question updated successfully.');
        } else {
            // Check for duplicate before create
            $exists = TestQuestion::where('test_id', $this->test_id)->where('question_id', $this->question_id)->exists();

            if ($exists) {
                session()->flash('error', 'This question is already added to the test.');
            } else {
                TestQuestion::create($data);
                session()->flash('success', 'Test Question created successfully.');
            }
        }

        $this->resetFields();
        $this->activeTab = 'index';
    }

    public function delete($id)
    {
        TestQuestion::findOrFail($id)->delete();
        session()->flash('success', 'Test Question deleted successfully.');
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    public function getTestQuestionsProperty()
    {
        $query = TestQuestion::with(['test', 'question'])->when($this->search, function ($q) {
            $q->whereHas('test', fn($t) => $t->where('title', 'like', '%' . $this->search . '%'))->orWhereHas('question', fn($qq) => $qq->where('question_text', 'like', '%' . $this->search . '%'));
        });

        return $query->orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage);
    }

    public function getTestsProperty()
    {
        return Test::orderBy('title')->get();
    }

    public function getQuestionsListProperty()
    {
        return Question::orderBy('id', 'desc')->limit(50)->get();
    }
};
?>


<section class="space-y-6">
    @if ($activeTab === 'create' || $activeTab === 'edit')
        <div>
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold">
                    {{ $activeTab === 'create' ? 'Create New Test Question' : 'Edit Test Question' }}
                </h3>
                <flux:button wire:click="showTab('index')" size="sm">Back</flux:button>
            </div>

            <form wire:submit="save" class="grid grid-cols-1 gap-6">
                <flux:select wire:model="test_id" label="Test">
                    <option value="">Select Test</option>
                    @foreach ($this->tests as $test)
                        <option value="{{ $test->id }}">{{ $test->title }}</option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="question_id" label="Question">
                    <option value="">Select Question</option>
                    @foreach ($this->questionsList as $q)
                        <option value="{{ $q->id }}">{{ Str::limit($q->question_text, 60) }}</option>
                    @endforeach
                </flux:select>

                <flux:input type="number" wire:model="order" label="Order" />

                <div class="mt-6 flex justify-end space-x-3">
                    <flux:button type="button" wire:click="showTab('index')">Cancel</flux:button>
                    <flux:button type="submit">{{ $activeTab === 'create' ? 'Create' : 'Update' }}</flux:button>
                </div>
            </form>
        </div>
    @else
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
            <h2 class="text-2xl font-bold">Test Question Management</h2>
            <div class="flex space-x-2">
                <flux:button wire:click="showTab('create')">Create New</flux:button>
            </div>
        </div>

        {{-- Search --}}
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search by test or question..." />

        {{-- Table --}}
        <div class="overflow-x-auto rounded-lg shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase cursor-pointer"
                            wire:click="sortBy('id')">
                            ID {!! $sortField === 'id' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' !!}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">Test</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">Question</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">Order</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($this->testQuestions as $tq)
                        <tr>
                            <td class="px-6 py-4">{{ $tq->id }}</td>
                            <td class="px-6 py-4">{{ $tq->test->title ?? '-' }}</td>
                            <td class="px-6 py-4 truncate max-w-xs">
                                {{ Str::limit($tq->question->question_text ?? '-', 50) }}</td>
                            <td class="px-6 py-4">{{ $tq->order }}</td>
                            <td class="px-6 py-4 text-right">
                                <button wire:click="edit({{ $tq->id }})" class="text-blue-600 mr-2">Edit</button>
                                <button wire:click="delete({{ $tq->id }})" class="text-red-600"
                                    onclick="return confirm('Are you sure to delete?')">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center">No records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination --}}
            @if ($this->testQuestions->hasPages())
                <div class="px-6 py-3 border-t">
                    {{ $this->testQuestions->links() }}
                </div>
            @endif
        </div>
    @endif
</section>
