<?php

use Illuminate\Support\Facades\Route;
use Modules\CRM\Http\Controllers\ContactController;
use Modules\CRM\Http\Controllers\LeadController;
use Modules\CRM\Http\Controllers\DealController;
use Modules\CRM\Http\Controllers\PipelineController;
use Modules\CRM\Http\Controllers\TaskController;
use Modules\CRM\Http\Controllers\ActivityController;
use Modules\CRM\Http\Controllers\InboxController;
use Modules\CRM\Http\Controllers\CampaignController;
use Modules\CRM\Http\Controllers\CallController;

/*
|--------------------------------------------------------------------------
| CRM Module Web Routes
|--------------------------------------------------------------------------
*/

// Contacts
Route::resource('contacts', ContactController::class);
Route::post('contacts/{contact}/toggle-status', [ContactController::class, 'toggleStatus'])->name('contacts.toggle-status');
Route::get('contacts/{contact}/activities', [ContactController::class, 'activities'])->name('contacts.activities');
Route::post('contacts/import', [ContactController::class, 'import'])->name('contacts.import');
Route::get('contacts/export', [ContactController::class, 'export'])->name('contacts.export');

// Leads (static routes must come before the resource to avoid {lead} wildcard conflicts)
Route::get('leads/kanban', [LeadController::class, 'kanban'])->name('leads.kanban');
Route::resource('leads', LeadController::class);
Route::post('leads/{lead}/convert', [LeadController::class, 'convert'])->name('leads.convert');
Route::post('leads/{lead}/assign', [LeadController::class, 'assign'])->name('leads.assign');
Route::post('leads/{lead}/move-stage', [LeadController::class, 'moveStage'])->name('leads.move-stage');
Route::post('leads/{lead}/mark-lost', [LeadController::class, 'markLost'])->name('leads.mark-lost');
Route::post('leads/{lead}/qualify', [LeadController::class, 'qualify'])->name('leads.qualify');
Route::get('leads/{lead}/suggestions', [LeadController::class, 'suggestions'])->name('leads.suggestions');
Route::post('leads/{lead}/reanalyze', [LeadController::class, 'reanalyze'])->name('leads.reanalyze');

// Deals
Route::resource('deals', DealController::class);
Route::get('deals/kanban', [DealController::class, 'kanban'])->name('deals.kanban');
Route::post('deals/{deal}/move-stage', [DealController::class, 'moveStage'])->name('deals.move-stage');
Route::post('deals/{deal}/mark-won', [DealController::class, 'markWon'])->name('deals.mark-won');
Route::post('deals/{deal}/mark-lost', [DealController::class, 'markLost'])->name('deals.mark-lost');
Route::get('deals/{deal}/commission', [DealController::class, 'commission'])->name('deals.commission');

// Pipelines
Route::resource('pipelines', PipelineController::class);
Route::post('pipelines/{pipeline}/stages/reorder', [PipelineController::class, 'reorderStages'])->name('pipelines.stages.reorder');
Route::resource('pipelines.stages', PipelineController::class)->shallow();

// Tasks
Route::resource('tasks', TaskController::class);
Route::post('tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
Route::post('tasks/{task}/reschedule', [TaskController::class, 'reschedule'])->name('tasks.reschedule');
Route::get('tasks/calendar', [TaskController::class, 'calendar'])->name('tasks.calendar');

// Activities
Route::resource('activities', ActivityController::class)->only(['index', 'store', 'show', 'destroy']);
Route::get('activities/timeline/{type}/{id}', [ActivityController::class, 'timeline'])->name('activities.timeline');

// Unified Inbox
Route::get('inbox', [InboxController::class, 'index'])->name('inbox.index');
Route::get('inbox/{conversation}', [InboxController::class, 'show'])->name('inbox.show');
Route::post('inbox/{conversation}/send', [InboxController::class, 'send'])->name('inbox.send');
Route::post('inbox/{conversation}/assign', [InboxController::class, 'assign'])->name('inbox.assign');
Route::post('inbox/{conversation}/status', [InboxController::class, 'updateStatus'])->name('inbox.status');

// Calls — AI transcription
Route::post('calls/transcribe', [CallController::class, 'transcribe'])->name('calls.transcribe');
Route::post('calls/activities/{activity}/transcribe', [CallController::class, 'transcribeActivity'])->name('calls.activity.transcribe');

// Drip Campaigns
Route::get('campaigns', [CampaignController::class, 'index'])->name('campaigns.index');
Route::get('campaigns/{campaign}', [CampaignController::class, 'show'])->name('campaigns.show');
Route::post('campaigns/{campaign}/toggle', [CampaignController::class, 'toggleActive'])->name('campaigns.toggle');
Route::post('campaigns/{campaign}/enroll', [CampaignController::class, 'enroll'])->name('campaigns.enroll');
Route::post('campaigns/enrollments/{enrollment}/cancel', [CampaignController::class, 'cancelEnrollment'])->name('campaigns.enrollments.cancel');
Route::post('campaigns/tick', [CampaignController::class, 'tick'])->name('campaigns.tick');
