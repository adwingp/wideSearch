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
        \App\Models\SearchLog::create([
            'query' => $query,
            'ip_address' => request()?->ip() ?? null,
            'created_at' => now(),
        ]);
        $results = collect();
        $blogPosts = BlogPost::search($query)->get()->map(function ($item) {
            return [
                'type' => 'blog_post',
                'title' => $item->title,
                'snippet' => Str::limit($item->body, 100),
                'link' => url('/blog/' . $item->id),
                'published_at' => $item->published_at,
                'created_at' => $item->created_at,
            ];
        });
        $results = $results->concat($blogPosts);
        $products = Product::search($query)->get()->map(function ($item) {
            return [
                'type' => 'product',
                'title' => $item->name,
                'snippet' => Str::limit($item->description, 100),
                'link' => url('/products/' . $item->id),
                'price' => $item->price,
                'created_at' => $item->created_at,
            ];
        });
        $results = $results->concat($products);
        $pages = Page::search($query)->get()->map(function ($item) {
            return [
                'type' => 'page',
                'title' => $item->title,
                'snippet' => Str::limit($item->content, 100),
                'link' => url('/pages/' . $item->id),
                'created_at' => $item->created_at,
            ];
        });
        $results = $results->concat($pages);
        $faqs = FAQ::search($query)->get()->map(function ($item) {
            return [
                'type' => 'faq',
                'title' => $item->question,
                'snippet' => Str::limit($item->answer, 100),
                'link' => url('/faqs/' . $item->id),
                'created_at' => $item->created_at,
            ];
        });
        $results = $results->concat($faqs);
        $results = $results->sortByDesc(function ($item) {
            return $item['published_at'] ?? $item['created_at'] ?? null;
        })->values();
        return \App\Services\PaginationHelper::paginate($results, $perPage, $page, $query, 'data');
    }

    public function suggestions(string $query, int $perPage = 20, int $page = 1): array
    {
        if (!$query) {
            return \App\Services\PaginationHelper::paginate([], $perPage, $page, $query, 'suggestions');
        }
        $suggestions = [];
        $blogPosts = \App\Models\BlogPost::where('title', 'like', "%$query%")
            ->pluck('title')->toArray();
        $suggestions = array_merge($suggestions, $blogPosts);
        $products = \App\Models\Product::where('name', 'like', "%$query%")
            ->pluck('name')->toArray();
        $suggestions = array_merge($suggestions, $products);
        $pages = \App\Models\Page::where('title', 'like', "%$query%")
            ->pluck('title')->toArray();
        $suggestions = array_merge($suggestions, $pages);
        $faqs = \App\Models\FAQ::where('question', 'like', "%$query%")
            ->pluck('question')->toArray();
        $suggestions = array_merge($suggestions, $faqs);
        $suggestions = array_values(array_unique($suggestions));
        return \App\Services\PaginationHelper::paginate($suggestions, $perPage, $page, $query, 'suggestions');
    }

    public function logs(int $perPage = 30, int $page = 1): array
    {
        $query = \App\Models\SearchLog::orderByDesc('created_at');
        $logs = $query->get(['query', 'ip_address', 'created_at']);
        return \App\Services\PaginationHelper::paginate($logs, $perPage, $page, null, 'logs');
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
