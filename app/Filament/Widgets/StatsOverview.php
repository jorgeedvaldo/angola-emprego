<?php

namespace App\Filament\Widgets;

use App\Models\Job;
use App\Models\Post;
use App\Models\Company;
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
            Card::make('Empresas cadastradas', Company::count())
                ->description(Company::where('approval_status', 'pending')->count() . ' aguardam aprovação')
                ->descriptionIcon('heroicon-s-office-building')
                ->color('primary'),
            Card::make('Empresas aprovadas', Company::where('approval_status', 'approved')->count())
                ->description('Perfis autorizados')
                ->descriptionIcon('heroicon-s-check-circle')
                ->color('success'),
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
