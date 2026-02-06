<?php

namespace App\Livewire\Website\Comments;

use Livewire\Component;
use App\Models\Comment;
use Livewire\WithPagination;

class CommentsSection extends Component
{
    use WithPagination;

    public $model;
    public $modelType;
    public $modelId;
    public $perPage = 10;

    protected $listeners = [
        'commentAdded' => 'handleCommentAdded',
        'commentDeleted' => 'handleCommentDeleted',
        'replyAdded' => 'handleReplyAdded'
    ];

    public function mount($model)
    {
        $this->model = $model;
        $this->modelType = get_class($model);
        $this->modelId = $model->id;
    }

    public function handleCommentAdded()
    {
        $this->render();
    }

    public function handleCommentDeleted($commentId)
    {
        $this->render();
    }

    public function handleReplyAdded($commentId)
    {
        $this->render();
    }

    public function render()
    {
        $comments = Comment::where('commentable_id', $this->modelId)
            ->where('commentable_type', $this->modelType)
            ->whereNull('parent_id')
            ->with(['user', 'replies.user'])
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.website.comments.comments-section', [
            'comments' => $comments,
        ]);
    }

}
