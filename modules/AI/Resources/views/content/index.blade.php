@extends('layouts.admin')

@section('title', 'AI Content Generator')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">AI Content Generator</h1>
            <p class="text-dark-400 mt-1">Generate professional property descriptions and marketing content</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Content Generator Form -->
        <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
            <h2 class="text-lg font-semibold text-white mb-6">Generate Content</h2>
            <form action="{{ route('ai.content.generate') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-2">Content Type</label>
                    <select name="content_type" class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="listing_description">Listing Description</option>
                        <option value="social_media">Social Media Post</option>
                        <option value="email">Email Template</option>
                        <option value="sms">SMS Message</option>
                        <option value="brochure">Brochure Text</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-2">Select Listing (Optional)</label>
                    <select name="listing_id" class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="">-- Select a listing --</option>
                        @foreach($listings ?? [] as $listing)
                        <option value="{{ $listing->id }}">{{ $listing->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-2">Property Details</label>
                    <textarea name="property_details" rows="4" placeholder="Enter property details: type, size, rooms, features, location..." class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-2">Tone</label>
                    <div class="flex flex-wrap gap-2">
                        <label class="inline-flex items-center">
                            <input type="radio" name="tone" value="professional" checked class="sr-only peer">
                            <span class="px-4 py-2 bg-dark-800 border border-dark-700 rounded-xl text-dark-300 peer-checked:bg-primary-600/20 peer-checked:border-primary-500/30 peer-checked:text-primary-400 cursor-pointer transition-colors">Professional</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="tone" value="luxury" class="sr-only peer">
                            <span class="px-4 py-2 bg-dark-800 border border-dark-700 rounded-xl text-dark-300 peer-checked:bg-primary-600/20 peer-checked:border-primary-500/30 peer-checked:text-primary-400 cursor-pointer transition-colors">Luxury</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="tone" value="friendly" class="sr-only peer">
                            <span class="px-4 py-2 bg-dark-800 border border-dark-700 rounded-xl text-dark-300 peer-checked:bg-primary-600/20 peer-checked:border-primary-500/30 peer-checked:text-primary-400 cursor-pointer transition-colors">Friendly</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="tone" value="urgent" class="sr-only peer">
                            <span class="px-4 py-2 bg-dark-800 border border-dark-700 rounded-xl text-dark-300 peer-checked:bg-primary-600/20 peer-checked:border-primary-500/30 peer-checked:text-primary-400 cursor-pointer transition-colors">Urgent</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-2">Language</label>
                    <select name="language" class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="tr">Turkish</option>
                        <option value="en">English</option>
                        <option value="de">German</option>
                        <option value="ru">Russian</option>
                        <option value="ar">Arabic</option>
                    </select>
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full px-6 py-3 bg-gradient-to-r from-purple-600 to-purple-500 hover:from-purple-700 hover:to-purple-600 text-white font-medium rounded-xl transition-all flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                        Generate Content
                    </button>
                </div>
            </form>
        </div>

        <!-- Generated Content -->
        <div class="space-y-6">
            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-white">Generated Content</h2>
                    <div class="flex items-center space-x-2">
                        <button class="p-2 text-dark-400 hover:text-white hover:bg-dark-700 rounded-lg transition-colors" title="Copy">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                        </button>
                        <button class="p-2 text-dark-400 hover:text-white hover:bg-dark-700 rounded-lg transition-colors" title="Regenerate">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="bg-dark-800/50 rounded-xl p-4 min-h-[300px]">
                    <p class="text-dark-400 text-center py-12">
                        Generated content will appear here...
                    </p>
                </div>
            </div>

            <!-- Quick Templates -->
            <div class="bg-gradient-to-br from-purple-900/30 to-dark-900 border border-purple-500/30 rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Quick Templates</h3>
                <div class="grid grid-cols-2 gap-3">
                    <button class="p-3 bg-dark-800/50 hover:bg-dark-700/50 rounded-xl text-left transition-colors">
                        <p class="text-white font-medium text-sm">New Listing</p>
                        <p class="text-dark-400 text-xs">Announce new property</p>
                    </button>
                    <button class="p-3 bg-dark-800/50 hover:bg-dark-700/50 rounded-xl text-left transition-colors">
                        <p class="text-white font-medium text-sm">Price Reduced</p>
                        <p class="text-dark-400 text-xs">Price drop announcement</p>
                    </button>
                    <button class="p-3 bg-dark-800/50 hover:bg-dark-700/50 rounded-xl text-left transition-colors">
                        <p class="text-white font-medium text-sm">Open House</p>
                        <p class="text-dark-400 text-xs">Event invitation</p>
                    </button>
                    <button class="p-3 bg-dark-800/50 hover:bg-dark-700/50 rounded-xl text-left transition-colors">
                        <p class="text-white font-medium text-sm">Just Sold</p>
                        <p class="text-dark-400 text-xs">Success story</p>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
