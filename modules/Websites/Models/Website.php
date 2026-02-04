<?php

namespace Modules\Websites\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Website extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'domain',
        'subdomain',
        'theme',
        'logo',
        'favicon',
        'primary_color',
        'secondary_color',
        'font_family',
        'settings',
        'seo_settings',
        'social_links',
        'contact_info',
        'analytics_id',
        'gtm_id',
        'facebook_pixel_id',
        'custom_css',
        'custom_js',
        'header_scripts',
        'footer_scripts',
        'is_active',
        'is_published',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'seo_settings' => 'array',
            'social_links' => 'array',
            'contact_info' => 'array',
            'is_active' => 'boolean',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function tenant()
    {
        return $this->belongsTo(\Modules\Core\Models\Tenant::class);
    }

    public function pages()
    {
        return $this->hasMany(Page::class);
    }

    public function forms()
    {
        return $this->hasMany(Form::class);
    }

    public function getFullUrlAttribute(): string
    {
        if ($this->domain) {
            return "https://{$this->domain}";
        }
        return "https://{$this->subdomain}." . config('app.domain');
    }
}
