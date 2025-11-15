<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Page extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'content',
        'editor_mode',
        'featured_image',
        'meta_description',
        'meta_keywords',
        'is_published',
        'published_at',
        'author_id',
        'page_type',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'sort_order' => 'integer',
        'content' => 'array',
    ];

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($page) {
            if (empty($page->slug)) {
                $page->slug = Str::slug($page->title);
            }
            if (empty($page->published_at) && $page->is_published) {
                $page->published_at = now();
            }
        });

        static::updating(function ($page) {
            if (empty($page->slug)) {
                $page->slug = Str::slug($page->title);
            }
            if ($page->is_published && empty($page->published_at)) {
                $page->published_at = now();
            }
        });
    }

    /**
     * Get the author of the page.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Scope a query to only include published pages.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope a query to only include draft pages.
     */
    public function scopeDraft($query)
    {
        return $query->where('is_published', false);
    }

    /**
     * Scope a query to filter by page type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('page_type', $type);
    }

    /**
     * Get the route key name.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get the page URL.
     */
    public function getUrlAttribute(): string
    {
        // Use specific named routes for common pages
        $specificRoutes = [
            'privacy-policy' => 'privacy-policy',
            'terms-of-service' => 'terms-of-service',
        ];

        if (isset($specificRoutes[$this->slug])) {
            return route($specificRoutes[$this->slug]);
        }

        // Use generic page route for other pages
        return route('page.show', $this->slug);
    }

    /**
     * Check if page is policy/legal type.
     */
    public function isPolicyPage(): bool
    {
        return in_array($this->page_type, ['policy', 'legal']);
    }

    /**
     * Get the content as HTML string for display.
     */
    public function getContentHtmlAttribute(): string
    {
        $content = $this->content;

        // If content is a string (from old data or code editor), return as-is
        if (is_string($content)) {
            return $content;
        }

        // If content is an array (from rich editor mode), convert to HTML
        if (is_array($content)) {
            // Check if it's Tiptap format (has 'type' key)
            if (isset($content['type'])) {
                return $this->convertTiptapToHtml($content);
            }
            // Otherwise, it might be JSON-encoded HTML string
            return implode('', $content);
        }

        return '';
    }

    /**
     * Convert Tiptap JSON to HTML.
     */
    private function convertTiptapToHtml(array $content): string
    {
        if (! isset($content['type'])) {
            return '';
        }

        $html = '';

        if ($content['type'] === 'doc' && isset($content['content'])) {
            foreach ($content['content'] as $node) {
                $html .= $this->renderNode($node);
            }
        }

        return $html;
    }

    /**
     * Render a Tiptap node to HTML.
     */
    private function renderNode(array $node): string
    {
        $type = $node['type'] ?? '';
        $content = $node['content'] ?? [];
        $attrs = $node['attrs'] ?? [];
        $marks = $node['marks'] ?? [];

        switch ($type) {
            case 'paragraph':
                $inner = $this->renderChildren($content, $marks);

                return "<p>{$inner}</p>";
            case 'heading':
                $level = $attrs['level'] ?? 2;
                $inner = $this->renderChildren($content, $marks);

                return "<h{$level}>{$inner}</h{$level}>";
            case 'bulletList':
                $inner = $this->renderChildren($content, $marks);

                return "<ul>{$inner}</ul>";
            case 'orderedList':
                $inner = $this->renderChildren($content, $marks);

                return "<ol>{$inner}</ol>";
            case 'listItem':
                $inner = $this->renderChildren($content, $marks);

                return "<li>{$inner}</li>";
            case 'blockquote':
                $inner = $this->renderChildren($content, $marks);

                return "<blockquote>{$inner}</blockquote>";
            case 'codeBlock':
                $inner = $this->renderChildren($content, $marks);

                return "<pre><code>{$inner}</code></pre>";
            case 'text':
                $text = $node['text'] ?? '';

                return $this->applyMarks($text, $marks);
            case 'hardBreak':
                return '<br>';
            default:
                return '';
        }
    }

    /**
     * Render child nodes.
     */
    private function renderChildren(array $children, array $parentMarks = []): string
    {
        $html = '';
        foreach ($children as $child) {
            $html .= $this->renderNode($child);
        }

        return $html;
    }

    /**
     * Apply text marks (bold, italic, etc).
     */
    private function applyMarks(string $text, array $marks): string
    {
        foreach ($marks as $mark) {
            $type = $mark['type'] ?? '';
            switch ($type) {
                case 'bold':
                    $text = "<strong>{$text}</strong>";
                    break;
                case 'italic':
                    $text = "<em>{$text}</em>";
                    break;
                case 'underline':
                    $text = "<u>{$text}</u>";
                    break;
                case 'strike':
                    $text = "<s>{$text}</s>";
                    break;
                case 'link':
                    $href = $mark['attrs']['href'] ?? '#';
                    $text = "<a href=\"{$href}\">{$text}</a>";
                    break;
                case 'code':
                    $text = "<code>{$text}</code>";
                    break;
            }
        }

        return $text;
    }
}
