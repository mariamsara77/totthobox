<?php

namespace App\Livewire\Website\Comments;

use Livewire\Component;
use App\Models\Comment;

class CommentItem extends Component
{

    public $comment;
    public $showReplyForm = false;
    public $replyContent = '';

    public array $openMenus = [];


    public $listeners = [
        'replyAdded' => '$refresh',
        'commentAdded' => '$refresh'
    ];

    public function mount(Comment $comment)
    {
        $this->comment = $comment;
    }

    public function toggleReplyForm()
    {
        $this->showReplyForm = !$this->showReplyForm;
        if (!$this->showReplyForm) {
            $this->replyContent = '';
        }
    }

    public function submitReply()
    {
        $this->validate([
            'replyContent' => 'required|min:3|max:500',
        ]);

        // Create the reply
        $reply = Comment::create([
            'content' => $this->replyContent,
            'user_id' => auth()->id(),
            'parent_id' => $this->comment->id,
            'commentable_id' => $this->comment->commentable_id,
            'commentable_type' => $this->comment->commentable_type,
            'depth' => $this->comment->depth + 1,
        ]);

        // Reset form
        $this->showReplyForm = false;
        $this->replyContent = '';

        // Refresh comments
        $this->dispatch('replyAdded', commentId: $this->comment->id);
        $this->dispatch(
            'notify',
            success: 'Reply added successfully!',
            type: 'success'
        );

        session()->flash('success', 'Reply added successfully!');
    }




    public function deleteComment()
    {
        // Authorization check
        if ($this->comment->user_id !== auth()->id() && !(auth()->user()->is_admin ?? false)) {
            $this->dispatch(
                'notify',
                message: 'You are not authorized to delete this comment.',
                type: 'error'
            );
            return;
        }

        $this->comment->delete();
        $this->dispatch('commentDeleted', commentId: $this->comment->id);
        $this->dispatch(
            'notify',
            message: 'Comment deleted successfully!',
            type: 'success'
        );
    }


    public function react($type)
    {
        // Login check
        if (!auth()->check()) {
            // Bangla flash message এবং redirect
            session()->flash('error', 'রিয়্যাকশন করার জন্য লগইন করতে হবে।');
            return redirect()->route('login');
        }

        // Reaction perform
        $this->comment->react($type);

        // Refresh comment to update counts in UI
        $this->comment->refresh();

        // Bangla success message
        session()->flash('success', 'আপনার রিয়্যাকশন সফলভাবে রেকর্ড করা হয়েছে!');
    }


     public function toggleMenu($key)
    {
        $this->openMenus[$key] = !($this->openMenus[$key] ?? false);
    }


    public function render()
    {
        // Load fresh data with replies
        $this->comment->load(['replies.user', 'user']);

        return view('livewire.website.comments.comment-item', [
            'comment' => $this->comment,
            'showReplyForm' => $this->showReplyForm,
            'replyContent' => $this->replyContent,
        ]);
    }

}
