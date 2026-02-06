<?php

use Livewire\Volt\Component;
use App\Models\ClassLevel;

new class extends Component {
    public $selectedClass;
    public $subjects = [];

    public function mount()
    {
        $this->selectedClass = ClassLevel::where('is_active', true)
            ->orderBy('order') // যেটা আগে দেখাচ্ছে
            ->value('id'); // সেটাই select হবে

        $this->loadSubjects();
    }

    public function selectClass($classId)
    {
        $this->selectedClass = $classId;
        $this->loadSubjects();
    }

    public function loadSubjects()
    {
        if ($this->selectedClass) {
            $this->subjects = ClassLevel::find($this->selectedClass)->subjects()->where('is_active', true)->get();
        }
    }

    public function getClassesProperty()
    {
        return ClassLevel::where('is_active', true)->orderBy('order')->get();
    }
};
?>

<div class="max-w-2xl mx-auto">
    <flux:heading size="xl">Select Your Class</flux:heading>

    <!-- Class Tabs -->
    <!-- Scrollable Tabs -->
    <div x-ref="tabs" class="flex gap-2 overflow-x-auto no-scrollbar py-2 px-0">
        @foreach ($this->classes as $class)
            <flux:button wire:click="selectClass({{ $class->id }})" size="sm" class="!rounded-full"
                variant="{{ $selectedClass == $class->id ? 'primary' : 'filled' }}">
                {{ $class->name }}
            </flux:button>
        @endforeach
    </div>

    <!-- Subjects -->
    <flux:heading class="my-3">Subjects</flux:heading>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($subjects as $subject)
            <div class="border border-zinc-400/25 p-4 rounded-xl transition space-y-2 hover:shadow-md">
                <flux:heading>{{ $subject->name }}</flux:heading>
                <flux:text>Total Tests: {{ $subject->tests()->count() }}</flux:text>
                <flux:link href="{{ route('mcq.subject', $subject->id) }}">View Tests</flux:link>
            </div>
        @empty
            <p class="text-gray-500">No subjects found for this class.</p>
        @endforelse
    </div>
</div>
