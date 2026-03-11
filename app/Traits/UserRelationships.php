<?php

namespace App\Traits;

use App\Models\BasicHealth;
use App\Models\BasicIslam;
use App\Models\Block;
use App\Models\BuySellCategory;
use App\Models\BuySellImage;
use App\Models\BuySellItem;
use App\Models\BuySellPost;
use App\Models\ClassLevel;
use App\Models\Comment;
use App\Models\ContactNumber;
use App\Models\District;
use App\Models\Division;
use App\Models\Dowa;
use App\Models\EstablishmentBd;
use App\Models\ExcelTutorial;
use App\Models\Food;
use App\Models\FoodCategory;
use App\Models\FoodDescribe;
use App\Models\FoodNutrient;
use App\Models\HistoryBd;
use App\Models\Holiday;
use App\Models\Hospital;
use App\Models\Institution;
use App\Models\IntroBd;
use App\Models\Item;
use App\Models\Message;
use App\Models\Minister;
use App\Models\Notification;
use App\Models\Nutrient;
use App\Models\PageView;
use App\Models\Para;
use App\Models\Quran;
use App\Models\Reaction;
use App\Models\Session;
use App\Models\Sign;
use App\Models\SignCategory;
use App\Models\Subject;
use App\Models\SupportMessage;
use App\Models\Sura;
use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\Thana;
use App\Models\TourismBd;
use App\Models\User;
use App\Models\UserReport;
use App\Models\UserTestAttempt;
use App\Models\Visitor;
use App\Models\VisitorEvent;
use App\Models\VisitorSession;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait UserRelationships
{
    // Belongs To
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function thana(): BelongsTo
    {
        return $this->belongsTo(Thana::class);
    }

    public function classLevel(): BelongsTo
    {
        return $this->belongsTo(ClassLevel::class);
    }

    // Has Many
    public function buySellItems(): HasMany
    {
        return $this->hasMany(BuySellItem::class);
    }

    public function buySellPosts(): HasMany
    {
        return $this->hasMany(BuySellPost::class);
    }

    public function buySellCategories(): HasMany
    {
        return $this->hasMany(BuySellCategory::class);
    }

    public function buySellImages(): HasMany
    {
        return $this->hasMany(BuySellImage::class);
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function unreadMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id')->whereNull('read_at');
    }

    public function contactNumbers(): HasMany
    {
        return $this->hasMany(ContactNumber::class);
    }

    public function supportMessages(): HasMany
    {
        return $this->hasMany(SupportMessage::class);
    }

    public function tests(): HasMany
    {
        return $this->hasMany(Test::class);
    }

    public function testQuestions(): HasMany
    {
        return $this->hasMany(TestQuestion::class);
    }

    public function userTestAttempts(): HasMany
    {
        return $this->hasMany(UserTestAttempt::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    public function classLevels(): HasMany
    {
        return $this->hasMany(ClassLevel::class);
    }

    public function quran(): HasMany
    {
        return $this->hasMany(Quran::class);
    }

    public function sura(): HasMany
    {
        return $this->hasMany(Sura::class);
    }

    public function dowa(): HasMany
    {
        return $this->hasMany(Dowa::class);
    }

    public function para(): HasMany
    {
        return $this->hasMany(Para::class);
    }

    public function basicIslam(): HasMany
    {
        return $this->hasMany(BasicIslam::class);
    }

    public function basicHealth(): HasMany
    {
        return $this->hasMany(BasicHealth::class);
    }

    public function foods(): HasMany
    {
        return $this->hasMany(Food::class);
    }

    public function foodCategories(): HasMany
    {
        return $this->hasMany(FoodCategory::class);
    }

    public function foodDescribes(): HasMany
    {
        return $this->hasMany(FoodDescribe::class);
    }

    public function foodNutrients(): HasMany
    {
        return $this->hasMany(FoodNutrient::class);
    }

    public function nutrients(): HasMany
    {
        return $this->hasMany(Nutrient::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }
    public function userReports(): HasMany
    {
        return $this->hasMany(UserReport::class);
    }

    public function pageViews(): HasMany
    {
        return $this->hasMany(PageView::class);
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class);
    }

   
    public function visitors(): HasMany
    {
        return $this->hasMany(Visitor::class);
    }

    public function visitorEvents(): HasMany
    {
        return $this->hasMany(VisitorEvent::class);
    }

    public function visitorSessions(): HasMany
    {
        return $this->hasMany(VisitorSession::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }

    public function excelTutorials(): HasMany
    {
        return $this->hasMany(ExcelTutorial::class);
    }

    public function institutions(): HasMany
    {
        return $this->hasMany(Institution::class);
    }

    public function historyBd(): HasMany
    {
        return $this->hasMany(HistoryBd::class);
    }

    public function tourismBd(): HasMany
    {
        return $this->hasMany(TourismBd::class);
    }

    public function establishmentBd(): HasMany
    {
        return $this->hasMany(EstablishmentBd::class);
    }

    public function hospitals(): HasMany
    {
        return $this->hasMany(Hospital::class);
    }

    public function ministers(): HasMany
    {
        return $this->hasMany(Minister::class);
    }

    public function signs(): HasMany
    {
        return $this->hasMany(Sign::class);
    }

    public function signCategories(): HasMany
    {
        return $this->hasMany(SignCategory::class);
    }

    public function introbds(): HasMany
    {
        return $this->hasMany(IntroBd::class);
    }

    public function holidays(): HasMany
    {
        return $this->hasMany(Holiday::class);
    }

    // Belongs To Many
    public function blockedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'blocks', 'blocked_user_id', 'user_id');
    }

    public function blockedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'blocks', 'user_id', 'blocked_user_id');
    }
}