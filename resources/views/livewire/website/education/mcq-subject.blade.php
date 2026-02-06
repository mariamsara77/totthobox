<?php

use Livewire\Volt\Component;
use App\Models\Subject;
use App\Models\Test;

new class extends Component {
    public $subjectId;
    public $subject;
    public $tests = [];
    public $activeTab = 'current';
    public $isLoading = true;

    public function mount($subjectId)
    {
        $this->subjectId = $subjectId;
        $this->loadSubject();
    }

    public function loadSubject()
    {
        $this->subject = Subject::with('classLevel')->find($this->subjectId);

        if (!$this->subject) {
            return redirect()->route('home');
        }

        $this->loadTests();
    }

    public function loadTests()
    {
        $this->isLoading = true;

        $query = Test::where('subject_id', $this->subject->id)
            ->with(['subject', 'classLevel'])
            ->where('is_published', true);

        switch ($this->activeTab) {
            case 'current':
                $this->tests = $query
                    ->where(function ($q) {
                        $q->whereNull('start_time')->orWhere('start_time', '<=', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('end_time')->orWhere('end_time', '>=', now());
                    })
                    ->get();
                break;

            case 'upcoming':
                $this->tests = $query->where('start_time', '>', now())->get();
                break;

            case 'past':
                $this->tests = $query->where('end_time', '<', now())->get();
                break;
        }

        $this->isLoading = false;
    }

    public function changeTab($tab)
    {
        $this->activeTab = $tab;
        $this->loadTests();
    }
};
?>

<div class="max-w-2xl mx-auto">
    <flux:heading>{{ $subject?->name ?? 'Subject' }} Tests</flux:heading>
    <p class="text-gray-600 mb-4">Class: {{ $subject?->classLevel?->name ?? '-' }}</p>

    <!-- Tabs -->
    <div class="flex gap-2 mb-4">
        @foreach (['current' => 'Current', 'upcoming' => 'Upcoming', 'past' => 'Past'] as $tabKey => $tabLabel)
            <flux:button wire:click="changeTab('{{ $tabKey }}')" size="sm" class="!rounded-full"
                variant="{{ $activeTab == $tabKey ? 'primary' : 'filled' }}">
                {{ $tabLabel }}
            </flux:button>
        @endforeach
    </div>

    @if ($isLoading)
        <flux:heading>Loading tests...</flux:heading>
    @else
        @if (count($tests) > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($tests as $test)
                    <div class="border border-zinc-400/25 p-4 rounded-xl transition space-y-2 hover:shadow-md">
                        <flux:heading>{{ $test->title }}</flux:heading>
                        <flux:text>Duration: {{ $test->duration }} min</flux:text>
                        <flux:text>Questions: {{ $test->total_questions }}</flux:text>
                        <flux:text>
                            Start: {{ $test->start_time?->format('d M Y H:i') ?? 'N/A' }}<br>
                            End: {{ $test->end_time?->format('d M Y H:i') ?? 'N/A' }}
                        </flux:text>
                        <flux:link href="{{ route('mcq.take-test', $test->id) }}">
                            Take Test
                        </flux:link>
                    </div>
                @endforeach
            </div>
        @else
            <flux:text>No tests found for this tab.</flux:text>
        @endif
    @endif
</div>
