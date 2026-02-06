<?php

namespace App\Livewire\Website\Comments;

use Livewire\Component;
use App\Models\Comment;

class CommentForm extends Component
{

    public $model;
    public $content = '';

    protected $rules = [
        'content' => 'required|min:3|max:1000',
    ];

    public function mount($model)
    {
        $this->model = $model;
    }

    public function submit()
    {
        $this->validate();

        Comment::create([
            'content' => $this->content,
            'user_id' => auth()->id(),
            'commentable_id' => $this->model->id,
            'commentable_type' => get_class($this->model),
        ]);

        $this->content = '';
        $this->dispatch('commentAdded');

        session()->flash('success', 'Comment added successfully!');
    }

    public function render()
    {
        return view('livewire.website.comments.comment-form');
    }
}
