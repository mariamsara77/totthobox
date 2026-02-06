<?php

use Livewire\Volt\Component;
use App\Models\Test;
use App\Models\UserTestAttempt;
use Carbon\Carbon;

new class extends Component {
    public $testId;
    public $test;
    public $questions = [];
    public $currentIndex = 0;
    public $answers = [];
    public $timeLeft;
    public $attemptId;
    public $testStarted = false;
    public $testCompleted = false;
    public $loading = true;

    public function mount($testId)
    {
        $this->testId = $testId;
        $this->loading = true;

        $this->test = Test::with('questions')->find($this->testId);

        if (!$this->test || !$this->test->is_published) {
            return redirect()->route('home');
        }

        $this->questions = $this->test->questions->shuffle();
        $this->initializeAnswers();
        $this->loading = false;
    }

    public function initializeAnswers()
    {
        $this->answers = [];
        foreach ($this->questions as $question) {
            $this->answers[$question->id] = null;
        }
    }

    public function startTest()
    {
        $this->testStarted = true;
        $this->timeLeft = $this->test->duration * 60;

        $this->attemptId = UserTestAttempt::create([
            'user_id' => auth()->id(),
            'test_id' => $this->test->id,
            'started_at' => now(),
            'answers' => $this->answers,
        ])->id;

        $this->dispatch('start-timer');
    }

    public function updateTimer()
    {
        if ($this->timeLeft > 0) {
            $this->timeLeft--;

            if ($this->timeLeft % 60 === 0) {
                $this->saveAttempt();
            }
        } else {
            $this->submitTest();
        }
    }

    public function saveAttempt()
    {
        UserTestAttempt::where('id', $this->attemptId)->update([
            'answers' => $this->answers,
            'started_at' => now()->subSeconds($this->test->duration * 60 - $this->timeLeft),
        ]);
    }

    public function navigateQuestion($index)
    {
        if ($index >= 0 && $index < count($this->questions)) {
            $this->currentIndex = $index;
            $this->saveAttempt();
        }
    }

    public function submitTest()
    {
        if ($this->testCompleted) {
            return;
        }

        $correct = 0;
        $score = 0;

        foreach ($this->answers as $questionId => $answer) {
            $question = $this->questions->firstWhere('id', $questionId);
            if ($question && $answer === $question->correct_answer) {
                $correct++;
                $score += $question->marks;
            }
        }

        UserTestAttempt::where('id', $this->attemptId)->update([
            'completed_at' => now(),
            'score' => $score,
            'correct_answers' => $correct,
            'wrong_answers' => count($this->answers) - $correct,
            'answers' => $this->answers,
        ]);

        $this->testCompleted = true;
    }

    public function formatTime($seconds)
    {
        return sprintf('%02d:%02d', floor($seconds / 60), $seconds % 60);
    }
};
?>

<section class="max-w-2xl mx-auto space-y-4">


    <!-- Loading State -->
    @if ($loading)
        @include('partials.skeleton')
    @endif

    <!-- Test Intro Screen -->
    @if (!$loading && !$testStarted)
        <div class="space-y-6">
            <div class="rounded-xl overflow-hidden">

                <flux:heading class="flex gap-3 items-center justify-center">
                    <flux:icon icon="book-open" />{{ $test->title }}
                </flux:heading>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Test Details -->
                    <flux:callout icon="information-circle" heading="টেস্ট বিবরণ" color="indigo" inline>
                        <flux:callout.text class="space-y-2">
                            <li>বিষয়: {{ $test->subject->name }}</li>
                            <li>শ্রেণী:{{ $test->classLevel->name }}</li>
                            <li>প্রশ্ন সংখ্যা: {{ bn_num($test->total_questions) }}</li>
                            <li>সময়: {{ bn_num($test->duration) }} মিনিট</li>
                            <li>মোট নম্বর: {{ bn_num($test->total_marks) }}</li>
                        </flux:callout.text>
                    </flux:callout>

                    <!-- Instructions -->
                    <flux:callout icon="list-bullet" heading="নির্দেশাবলী" color="orange" inline>
                        <flux:callout.text>
                            <li>আপনার কাছে {{ bn_num($test->duration) }} মিনিট সময় থাকবে</li>
                            <li>প্রতিটি প্রশ্নের নম্বর প্রশ্নের সাথে উল্লেখ করা আছে</li>
                            <li>নেগেটিভ মার্কিং নেই</li>
                            <li>সময় শেষ হলে টেস্ট স্বয়ংক্রিয়ভাবে জমা হয়ে যাবে</li>
                            <li>আপনি যেকোনো সময় টেস্ট জমা দিতে পারবেন</li>
                        </flux:callout.text>
                    </flux:callout>
                </div>

                <div class="p-6 flex justify-center">
                    <flux:button wire:click="startTest" icon="play-circle">
                        <span wire:loading.remove><i class="fas fa-play-circle"></i>টেস্ট শুরু
                            করুন
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    <!-- Test In Progress -->
    @if (!$loading && $testStarted && !$testCompleted)
        <div class="space-y-4">

            <!-- Timer & Header -->
            <flux:header class="flex items-center justify-between">
                <flux:heading>{{ $test->title }}</flux:heading>
                <div class="flex items-center space-x-4 justify-between">
                    <flux:badge color="yellow">

                        {{ $this->formatTime($timeLeft) }}
                    </flux:badge>
                    <flux:button wire:click="submitTest" size="sm">
                        জমা দিন
                    </flux:button>
                </div>
            </flux:header>

            <!-- Questions -->
            @foreach ($questions as $index => $question)
                <div class="space-y-4 @if ($index > 0) border-t pt-4 @endif">
                    <flux:heading size="lg" class="font-semibold">
                        <flux:badge color="yellow">{{ $index + 1 }}</flux:badge>
                        {{ $question->question_text }}({{ bn_num($question->marks) }} নম্বর)
                    </flux:heading>

                    <div class="space-y-2">
                        <flux:radio.group wire:model="answers.{{ $question->id }}" class="space-y-3">
                            @foreach (['a', 'b', 'c', 'd'] as $option)
                                @php
                                    // চেক করা হচ্ছে এই অপশনটি বর্তমানে সিলেক্টেড কি না
                                    $isSelected = ($answers[$question->id] ?? '') === $option;
                                @endphp

                                <label class="cursor-pointer block group">
                                    <flux:callout :color="$isSelected ? 'blue' : 'zinc'" class="duration-300 !p-0">
                                        <div class="flex items-center gap-4">
                                            <input type="radio" wire:model.live="answers.{{ $question->id }}"
                                                value="{{ $option }}" class="hidden" />

                                            <flux:badge color="{{ $isSelected ? 'blue' : 'gray' }}">
                                                {{ $option }}
                                            </flux:badge>


                                            <flux:heading size="lg"
                                                class="{{ $isSelected ? 'text-blue-400' : '' }}">
                                                {{ $question['option_' . $option] }}
                                            </flux:heading>

                                            @if ($isSelected)
                                                <div class="ml-auto">
                                                    <flux:icon.check-circle variant="solid" color="blue"
                                                        class="w-6 h-6" />
                                                </div>
                                            @endif
                                        </div>
                                    </flux:callout>
                                </label>
                            @endforeach
                        </flux:radio.group>
                    </div>
                </div>
            @endforeach
            <!-- Submit -->
            <flux:button wire:click="submitTest" icon="check-circle">
                সমস্ত উত্তর জমা দিন
            </flux:button>
        </div>
    @endif

    <!-- Test Completed -->
    @if (!$loading && $testCompleted)
        <div class="max-w-3xl mx-auto space-y-6">

            <flux:header class="justify-center gap-3 ">
                <flux:icon icon="check-circle" color="blue" class="w-8 h-8" />
                <flux:heading size="xl" class="text-blue-600">টেস্ট সম্পন্ন হয়েছে!</flux:heading>
            </flux:header>

            <flux:header class="gap-3">
                <flux:text size="xl" variant="strong">আপনার স্কোর:</flux:text>
                <flux:text size="xl" color="blue">{{ $test->attempts->find($attemptId)->score }}</flux:text>
                <flux:text size="xl" variant="subtle">/{{ $test->total_marks }}
                </flux:text>
            </flux:header>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:callout icon="check-circle" heading="সঠিক উত্তর" color="green" class="!border-0  w-full">
                    <flux:text class="text-4xl text-center" color="green">
                        {{ $test->attempts->find($attemptId)->correct_answers }}
                    </flux:text>
                </flux:callout>

                <flux:callout icon="x-mark" variant="solid" heading="সঠিক উত্তর" color="red"
                    class="!border-0 w-full">
                    <flux:text class="text-4xl text-center" color="red">
                        {{ $test->attempts->find($attemptId)->wrong_answers }}
                    </flux:text>
                </flux:callout>
            </div>

            <div class="flex flex-col md:flex-row justify-center gap-3 mt-6">
                <flux:button href="{{ route('mcq.test-result') }}?attempt={{ $attemptId }}" icon="eye">
                    বিস্তারিত ফলাফল দেখুন
                </flux:button>
                <flux:button href="{{ route('home') }}" variant="ghost" icon="home">
                    হোম পেজে ফিরে যান
                </flux:button>
            </div>
        </div>
    @endif


    @push('scripts')
        <script>
            document.addEventListener('livewire:initialized', () => {
                Livewire.on('start-timer', () => {
                    const timerInterval = setInterval(() => {
                        @this.updateTimer();
                        if (@this.testCompleted) {
                            clearInterval(timerInterval);
                        }
                    }, 1000);
                });

                window.addEventListener('beforeunload', function(e) {
                    if (@this.testStarted && !@this.testCompleted) {
                        e.preventDefault();
                        e.returnValue =
                            'You are refreshing the page during the test. Your answers may not be saved.';
                    }
                });
            });
        </script>
    @endpush

</section>
