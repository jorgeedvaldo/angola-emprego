<?php

namespace App\Filament\Widgets;

use App\Models\Job;
use App\Models\Post;
use App\Models\SubscriptionRequest;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class StatsOverview extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('Total Users', User::count())
                ->description('Total registered users')
                ->descriptionIcon('heroicon-s-users')
                ->color('primary'),
            Card::make('Subscription Requests', SubscriptionRequest::count())
                ->description('Total subscription requests')
                ->descriptionIcon('heroicon-s-ticket')
                ->color('success'),
            Card::make('Total Posts', Post::count())
                ->description('Total blog posts')
                ->descriptionIcon('heroicon-s-document-text')
                ->color('warning'),
            Card::make('Total Jobs', Job::count())
                ->description('Total job listings')
                ->descriptionIcon('heroicon-s-briefcase')
                ->color('danger'),
        ];
    }
}
