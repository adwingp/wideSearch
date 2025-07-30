<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Models\Product;
use App\Models\Page;
use App\Models\FAQ;
use Illuminate\Support\Str;

class SearchService
{
    public function unifiedSearch(string $query, int $perPage = 10, int $page = 1): array
    {
        // Log the search query to SearchLog
        \App\Models\SearchLog::create([
            'query' => $query,
            'ip_address' => request()?->ip() ?? null,
            'created_at' => now(),
        ]);

        $results = collect();

        // Blog Posts
        $blogPosts = BlogPost::search($query)->take(20)->get()->map(function ($item) {
            return [
                'type' => 'blog_post',
                'title' => $item->title,
                'snippet' => Str::limit($item->body, 100),
                'link' => url('/blog/' . $item->id),
                'created_at' => $item->created_at,
                'published_at' => $item->published_at,
            ];
        });
        $results = $results->concat($blogPosts);

        // Products
        $products = Product::search($query)->take(20)->get()->map(function ($item) {
            return [
                'type' => 'product',
                'title' => $item->name,
                'snippet' => Str::limit($item->description, 100),
                'link' => url('/products/' . $item->id),
                'created_at' => $item->created_at,
            ];
        });
        $results = $results->concat($products);

        // Pages
        $pages = Page::search($query)->take(20)->get()->map(function ($item) {
            return [
                'type' => 'page',
                'title' => $item->title,
                'snippet' => Str::limit($item->content, 100),
                'link' => url('/pages/' . $item->id),
                'created_at' => $item->created_at,
            ];
        });
        $results = $results->concat($pages);

        // FAQs
        $faqs = FAQ::search($query)->take(20)->get()->map(function ($item) {
            return [
                'type' => 'faq',
                'title' => $item->question,
                'snippet' => Str::limit($item->answer, 100),
                'link' => url('/faqs/' . $item->id),
                'created_at' => $item->created_at,
            ];
        });
        $results = $results->concat($faqs);

        // Sort by recency
        $results = $results->sortByDesc(function ($item) {
            return $item['published_at'] ?? $item['created_at'] ?? null;
        })->values();

        // Pagination
        $total = $results->count();
        $paginated = $results->forPage($page, $perPage)->values();

        return [
            'data' => $paginated,
            'meta' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'query' => $query,
            ],
        ];
    }

    public function suggestions(string $query): array
    {
        // Aggregate suggestions from all models except User
        if (!$query) {
            return ['suggestions' => []];
        }
        $suggestions = [];
        // BlogPost titles
        $blogPosts = \App\Models\BlogPost::where('title', 'like', "%$query%")
            ->limit(10)->pluck('title')->toArray();
        $suggestions = array_merge($suggestions, $blogPosts);
        // Product names
        $products = \App\Models\Product::where('name', 'like', "%$query%")
            ->limit(10)->pluck('name')->toArray();
        $suggestions = array_merge($suggestions, $products);
        // Page titles
        $pages = \App\Models\Page::where('title', 'like', "%$query%")
            ->limit(10)->pluck('title')->toArray();
        $suggestions = array_merge($suggestions, $pages);
        // FAQ questions
        $faqs = \App\Models\FAQ::where('question', 'like', "%$query%")
            ->limit(10)->pluck('question')->toArray();
        $suggestions = array_merge($suggestions, $faqs);
        // Remove duplicates and limit to 20
        $suggestions = array_values(array_unique($suggestions));
        return ['suggestions' => array_slice($suggestions, 0, 20)];
    }

    public function logs(): array
    {
        // Fetch latest search logs from SearchLog table
        $logs = \App\Models\SearchLog::orderByDesc('created_at')->limit(30)->get(['query', 'ip_address', 'created_at']);
        return ['logs' => $logs];
    }

    public function reindex(): array
    {
        // Reindex all searchable models
        \App\Models\BlogPost::makeAllSearchable();
        \App\Models\Product::makeAllSearchable();
        \App\Models\Page::makeAllSearchable();
        \App\Models\FAQ::makeAllSearchable();
        return [
            'message' => 'Reindex triggered for BlogPost, Product, Page, and FAQ.'
        ];
    }
}
