<?php

use Livewire\Volt\Component;
use App\Models\UserTestAttempt;
use App\Models\Test;

new class extends Component {
    public $attempts;
    public $selectedAttemptId;
    public $selectedAttempt;
    public $test;
    public $questions;
    public $userAnswers;

    public function mount()
    {
        $this->loadUserAttempts();
    }

    public function loadUserAttempts()
    {
        $this->attempts = UserTestAttempt::with(['test', 'test.subject', 'test.classLevel'])
            ->where('user_id', auth()->id())
            ->whereNotNull('completed_at')
            ->orderBy('completed_at', 'desc')
            ->get();

        if ($this->attempts->count() > 0 && !$this->selectedAttemptId) {
            $this->selectAttempt($this->attempts->first()->id);
        }
    }

    public function selectAttempt($attemptId)
    {
        $this->selectedAttemptId = $attemptId;
        $this->selectedAttempt = UserTestAttempt::with(['test.questions'])
            ->where('id', $attemptId)
            ->where('user_id', auth()->id())
            ->first();

        if ($this->selectedAttempt) {
            $this->test = $this->selectedAttempt->test;
            $this->questions = $this->selectedAttempt->test->questions;
            $this->userAnswers = $this->selectedAttempt->answers ?? [];
        }
    }

    public function isCorrect($questionId)
    {
        if (!isset($this->userAnswers[$questionId])) {
            return false;
        }

        $question = $this->questions->firstWhere('id', $questionId);
        return $question && $this->userAnswers[$questionId] === $question->correct_answer;
    }
};
?>


<section class="max-w-2xl mx-auto space-y-4">

    @if ($attempts && $attempts->count() > 0)

        <!-- Header -->
        <flux:heading size="xl">আপনার পরীক্ষার ফলাফল</flux:heading>

        <!-- Attempt Selection -->

        <flux:select wire:model.live="selectedAttemptId" wire:change="selectAttempt($event.target.value)"
            label="পরীক্ষা নির্বাচন করুন:">
            <option value="">-- একটি পরীক্ষা বেছে নিন --</option>
            @foreach ($attempts as $attempt)
                <option value="{{ $attempt->id }}">
                    {{ $attempt->test->title }}

                    @if ($attempt->completed_at)
                        | {{ bn_date($attempt->completed_at->format('d M, Y g:i A')) }}
                    @endif

                    | স্কোর: {{ $attempt->score }}/{{ $attempt->test->total_marks }}
                </option>
            @endforeach
        </flux:select>


        <!-- Selected Attempt -->
        @if ($selectedAttempt && $test)
            <!-- Score Summary -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center mb-6">
                <flux:callout icon="trophy" heading="মোট স্কোর" color="blue">
                    <flux:heading size="xl" class="text-4xl font-semibold text-blue-600">
                        {{ $selectedAttempt->score }}/{{ $test->total_marks }}</flux:heading>
                    <p class="text-gray-700"></p>
                    <flux:callout.text>
                        @if ($test->total_marks > 0)
                            {{ number_format(($selectedAttempt->score / $test->total_marks) * 100, 2) }}%
                        @else
                            0%
                        @endif
                    </flux:callout.text>
                </flux:callout>
                <flux:callout icon="check-circle" heading="সঠিক উত্তর" color="green">
                    <flux:heading size="xl" class="text-4xl font-semibold text-green-600">
                        {{ $selectedAttempt->correct_answers }}
                    </flux:heading>
                </flux:callout>
                <flux:callout icon="x-mark" heading="ভুল উত্তর" color="red">
                    <flux:heading size="xl" class="text-4xl font-semibold text-red-600">
                        {{ $selectedAttempt->wrong_answers }}</flux:heading>
                </flux:callout>
            </div>

            <flux:callout variant="info">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left column -->
                    <div class="space-y-1">
                        <flux:text>পরীক্ষা</flux:text>
                        <flux:heading level="4">{{ $test->title }}</flux:heading>

                        <flux:text>বিষয়</flux:text>
                        <flux:heading>{{ $test->subject->name }}</flux:heading>
                    </div>

                    <!-- Right column -->
                    <div class="space-y-1">
                        <flux:text>শ্রেণি</flux:text>
                        <flux:heading>{{ $test->classLevel->name }}</flux:heading>

                        <flux:text>সমাপ্তির তারিখ</flux:text>
                        <flux:heading>
                            @if ($selectedAttempt->completed_at)
                                {{ bn_date($selectedAttempt->completed_at->format('d M, Y g:i A')) }}
                            @else
                                <flux:badge color="yellow">অনুপলব্ধ</flux:badge>
                            @endif
                        </flux:heading>
                    </div>
                </div>
            </flux:callout>

            <!-- Question-wise Results -->
            <flux:heading size="lg" class="font-semibold">প্রশ্নভিত্তিক বিশ্লেষণ:</flux:heading>

            @foreach ($questions as $index => $question)
                <div class="border rounded-xl border-zinc-400/25 overflow-hidden" x-data="{ open: {{ $index === 0 ? 'true' : 'false' }} }">
                    <!-- Accordion header -->
                    <flux:header class="gap-3 items-center border-b border-zinc-400/25"
                        color="{{ $this->isCorrect($question->id) ? 'green' : 'red' }}" @click="open = !open">
                        <flux:heading>প্রশ্ন #{{ bn_num($index + 1) }}</flux:heading>
                        <flux:heading size="lg" class="font-semibold mb-2">{{ $question->question_text }}
                        </flux:heading>

                        <flux:badge color="{{ $this->isCorrect($question->id) ? 'green' : 'red' }}">
                            {{ $this->isCorrect($question->id) ? 'সঠিক' : 'ভুল' }}
                        </flux:badge>
                    </flux:header>

                    <!-- Accordion body -->
                    <div class="space-y-3 p-4" x-show="open" x-transition x-transition:enter.duration.500ms
                        x-transition:leave.duration.400ms x-cloak>


                        @foreach (['a', 'b', 'c', 'd'] as $option)
                            @php
                                $isCorrect = $question->correct_answer === $option;
                                $userSelected = ($userAnswers[$question->id] ?? null) === $option;
                                $isWrongSelection = $userSelected && !$isCorrect;

                                // ডিটারমাইন ভেরিয়েন্ট এবং আইকন
                                $variant = 'subtle';
                                $icon = null;
                                $class = 'border-gray-200 bg-gray-50';

                                if ($isCorrect) {
                                    $variant = 'success';
                                    $icon = 'check-circle'; // Flux icons ব্যবহার করলে
                                    $color = 'green';
                                } elseif ($isWrongSelection) {
                                    $variant = 'danger';
                                    $icon = 'x-mark';
                                    $color = 'red';
                                }
                            @endphp

                            <flux:callout :variant="$variant" class="">
                                <div class="flex items-center gap-2">
                                    @if ($isCorrect)
                                        <flux:icon name="{{ $icon }}" variant="solid"
                                            color="{{ $color }}" />
                                    @elseif ($isWrongSelection)
                                        <flux:icon name="{{ $icon }}" variant="solid"
                                            color="{{ $color }}" />
                                    @endif


                                    <flux:callout.text> <span class="uppercase">{{ $option }}:</span>
                                        {{ $question['option_' . $option] }}
                                    </flux:callout.text>

                                </div>
                            </flux:callout>
                        @endforeach

                        <!-- User's Answer -->
                        <flux:heading>আপনার উত্তর:
                            @if (isset($userAnswers[$question->id]))
                                <span>{{ strtoupper($userAnswers[$question->id]) }}</span>
                            @else
                                <span class="text-red-600">উত্তর দেননি</span>
                            @endif
                        </flux:heading>
                        <!-- Explanation -->
                        @if ($question->explanation)
                            <flux:header class="border-t border-zinc-400/25">
                                <strong>"ব্যাখ্যা:</strong> {{ $question->explanation }}
                            </flux:header>
                        @endif
                    </div>
                </div>
            @endforeach

        @endif
        <flux:separator />
        <!-- Floating Footer -->
        <div class="flex item-center justify-between gap-3">
            <flux:text>
                পরীক্ষা সম্পন্ন: {{ $selectedAttempt->completed_at->format('d/m/Y') ?? 'তারিখ নেই' }}
            </flux:text>

            <flux:button href="{{ route('home') }}" icon="home" variant="ghost">
                প্রস্থান
            </flux:button>
        </div>
    @else
        @include('partials.empty')
    @endif
</section>
