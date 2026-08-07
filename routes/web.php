<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\CaseStudyController;
use App\Http\Controllers\Admin\CaseStudyChildController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\LicenseController;
use App\Http\Controllers\Admin\HomepageController;
use App\Http\Controllers\Admin\PublicChromeController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PageSectionController;
use App\Http\Controllers\Admin\SolutionChildController;
use App\Http\Controllers\Admin\SolutionController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductChildController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\PublicArticleController;
use App\Http\Controllers\PublicCaseStudyController;
use App\Http\Controllers\PublicFaqController;
use App\Http\Controllers\PublicSolutionController;
use App\Http\Controllers\PublicProductController;
use App\Http\Controllers\PublicIndustryController;
use App\Http\Controllers\Admin\IndustryController;
use App\Http\Controllers\Admin\IndustryChildController;
use App\Http\Controllers\Admin\CapabilityController;
use App\Http\Controllers\Admin\CapabilityChildController;
use App\Http\Controllers\Admin\WorkProcessController;
use App\Http\Controllers\Admin\WorkProcessChildController;
use App\Http\Controllers\PublicCapabilityController;
use App\Http\Controllers\PublicWorkProcessController;
use App\Http\Controllers\PublicSiteController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\PublicClientController;
use App\Http\Controllers\PublicTestimonialController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\StatisticController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MediaCollectionController;
use App\Http\Controllers\Admin\MediaUsageController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', PublicSiteController::class)->name('home');
Route::get('sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('insights', [PublicArticleController::class, 'index'])->name('insights.index');
Route::get('insights/{article}', [PublicArticleController::class, 'show'])->where('article', '[a-z0-9]+(?:-[a-z0-9]+)*')->name('insights.show');
Route::get('case-studies', [PublicCaseStudyController::class, 'index'])->name('case-studies.index');
Route::get('case-studies/{caseStudy}', [PublicCaseStudyController::class, 'show'])->where('caseStudy', '[a-z0-9]+(?:-[a-z0-9]+)*')->name('case-studies.show');
Route::get('faq', PublicFaqController::class)->name('faq.index');
Route::get('clients', [PublicClientController::class, 'index'])->name('clients.index');
Route::get('clients/{client}', [PublicClientController::class, 'show'])->where('client', '[a-z0-9]+(?:-[a-z0-9]+)*')->name('clients.show');
Route::get('testimonials', PublicTestimonialController::class)->name('testimonials.index');
Route::get('capabilities', [PublicCapabilityController::class, 'index'])->name('capabilities.index');
Route::get('capabilities/{capability}', [PublicCapabilityController::class, 'show'])->where('capability', '[a-z0-9]+(?:-[a-z0-9]+)*')->name('capabilities.show');
Route::get('work-processes', [PublicWorkProcessController::class, 'index'])->name('work-processes.index');
Route::get('work-processes/{workProcess}', [PublicWorkProcessController::class, 'show'])->where('workProcess', '[a-z0-9]+(?:-[a-z0-9]+)*')->name('work-processes.show');
Route::get('industries', [PublicIndustryController::class, 'index'])->name('industries.index');
Route::get('industries/{industry}', [PublicIndustryController::class, 'show'])->where('industry', '[a-z0-9]+(?:-[a-z0-9]+)*')->name('industries.show');
Route::get('products', [PublicProductController::class, 'index'])->name('products.index');
Route::get('products/{product}', [PublicProductController::class, 'show'])->where('product', '[a-z0-9]+(?:-[a-z0-9]+)*')->name('products.show');
Route::get('solutions', [PublicSolutionController::class, 'index'])->name('solutions.index');
Route::get('solutions/{solution}', [PublicSolutionController::class, 'show'])->where('solution', '[a-z0-9]+(?:-[a-z0-9]+)*')->name('solutions.show');

Route::middleware(['auth', 'administrator.active', 'installed'])->group(function () {
    Route::get('dashboard', DashboardController::class)->middleware('verified')->name('dashboard');
    Route::get('system/license', [LicenseController::class, 'status'])->name('license.status');
    Route::get('system/license/diagnostics', [LicenseController::class, 'diagnostics'])->name('license.diagnostics');

    Route::prefix('content/media')->name('admin.media.')->group(function () {
        Route::get('/', [MediaController::class, 'index'])->name('index');
        Route::post('/', [MediaController::class, 'store'])->middleware('throttle:10,1')->name('store');
        Route::post('collections', [MediaCollectionController::class, 'store'])->middleware('throttle:30,1')->name('collections.store');
        Route::patch('collections/{collection}', [MediaCollectionController::class, 'update'])->middleware('throttle:30,1')->name('collections.update');
        Route::delete('collections/{collection}', [MediaCollectionController::class, 'destroy'])->middleware('throttle:30,1')->name('collections.destroy');
        Route::post('usages/{type}/{id}', [MediaUsageController::class, 'store'])->middleware('throttle:30,1')->name('usages.store');
        Route::delete('usages/{type}/{id}/{mediaUsage}', [MediaUsageController::class, 'destroy'])->middleware('throttle:30,1')->name('usages.destroy');
        Route::get('{mediaAsset}', [MediaController::class, 'show'])->name('show');
        Route::patch('{mediaAsset}', [MediaController::class, 'update'])->middleware('throttle:30,1')->name('update');
        Route::post('{mediaAsset}/replace', [MediaController::class, 'replace'])->middleware('throttle:10,1')->name('replace');
        Route::post('{mediaAsset}/archive', [MediaController::class, 'archive'])->middleware('throttle:30,1')->name('archive');
        Route::post('{mediaAsset}/restore', [MediaController::class, 'restore'])->middleware('throttle:30,1')->name('restore');
        Route::delete('{mediaAsset}', [MediaController::class, 'destroy'])->middleware('throttle:10,1')->name('destroy');
    });

    Route::prefix('website')->name('admin.')->middleware('throttle:60,1')->controller(PublicChromeController::class)->group(function () {
        Route::get('branding', 'branding')->name('branding.edit'); Route::put('branding', 'saveBranding')->name('branding.update');
        Route::get('header', 'header')->name('header.edit'); Route::put('header', 'saveHeader')->name('header.update');
        Route::get('navigation', 'navigation')->name('navigation.index'); Route::post('navigation', 'storeMenu')->name('navigation.store');
        Route::get('navigation/{menu}', 'editNavigation')->name('navigation.edit'); Route::put('navigation/{menu}', 'updateMenu')->name('navigation.update'); Route::delete('navigation/{menu}', 'deleteMenu')->name('navigation.destroy');
        Route::post('navigation/{menu}/items', 'saveItem')->name('navigation.items.store'); Route::put('navigation/{menu}/items/{item}', 'saveItem')->name('navigation.items.update'); Route::delete('navigation/{menu}/items/{item}', 'deleteItem')->name('navigation.items.destroy'); Route::post('navigation/{menu}/items/{item}/move', 'moveItem')->name('navigation.items.move');
        Route::get('footer', 'footer')->name('footer.edit'); Route::put('footer', 'saveFooter')->name('footer.update'); Route::post('footer/columns', 'storeColumn')->name('footer.columns.store'); Route::delete('footer/columns/{column}', 'deleteColumn')->name('footer.columns.destroy'); Route::post('footer/columns/{column}/links', 'storeFooterLink')->name('footer.links.store'); Route::delete('footer/links/{link}', 'deleteFooterLink')->name('footer.links.destroy'); Route::post('footer/social', 'storeSocial')->name('footer.social.store'); Route::delete('footer/social/{social}', 'deleteSocial')->name('footer.social.destroy');
    });

    Route::prefix('website/homepage')->name('admin.homepage.')->middleware('throttle:60,1')->controller(HomepageController::class)->group(function () {
        Route::get('/', 'edit')->name('edit');
        Route::put('/', 'saveSettings')->name('update');
        Route::post('publish', 'publish')->name('publish');
        Route::post('unpublish', 'unpublish')->name('unpublish');
        Route::get('preview', 'preview')->name('preview');
        Route::get('sections', 'edit')->name('sections');
        Route::get('{section}', 'section')->where('section', '[a-z_]+')->name('section');
        Route::put('sections/{section}', 'saveSection')->name('sections.update');
        Route::post('sections/{section}/move', 'move')->name('sections.move');
        Route::post('sections/{section}/items', 'storeItem')->name('items.store');
        Route::delete('sections/{section}/items/{item}', 'destroyItem')->name('items.destroy');
    });

    Route::prefix('website/pages')->name('admin.pages.')->middleware('throttle:60,1')->group(function () {
        Route::get('/', [PageController::class, 'index'])->name('index'); Route::get('create', [PageController::class, 'create'])->name('create'); Route::post('/', [PageController::class, 'store'])->name('store');
        Route::get('{page}/edit', [PageController::class, 'edit'])->name('edit'); Route::match(['put', 'patch'], '{page}', [PageController::class, 'update'])->name('update');
        Route::post('{page}/publish', [PageController::class, 'publish'])->name('publish'); Route::post('{page}/schedule', [PageController::class, 'schedule'])->name('schedule'); Route::post('{page}/unpublish', [PageController::class, 'unpublish'])->name('unpublish');
        Route::post('{page}/archive', [PageController::class, 'archive'])->name('archive'); Route::post('{page}/restore', [PageController::class, 'restore'])->name('restore'); Route::post('{page}/duplicate', [PageController::class, 'duplicate'])->name('duplicate');
        Route::post('{page}/move-up', [PageController::class, 'move'])->defaults('direction', 'up')->name('move-up'); Route::post('{page}/move-down', [PageController::class, 'move'])->defaults('direction', 'down')->name('move-down'); Route::get('{page}/preview', [PageController::class, 'preview'])->name('preview');
        Route::post('{page}/sections', [PageSectionController::class, 'store'])->name('sections.store'); Route::match(['put', 'patch'], '{page}/sections/{section}', [PageSectionController::class, 'update'])->name('sections.update'); Route::delete('{page}/sections/{section}', [PageSectionController::class, 'destroy'])->name('sections.destroy'); Route::post('{page}/sections/{section}/move', [PageSectionController::class, 'move'])->name('sections.move');
    });

    Route::prefix('content/solutions')->name('admin.solutions.')->middleware('throttle:60,1')->group(function () {
        Route::get('/', [SolutionController::class, 'index'])->name('index'); Route::get('create', [SolutionController::class, 'create'])->name('create'); Route::post('/', [SolutionController::class, 'store'])->name('store');
        Route::get('categories', [SolutionController::class, 'categories'])->name('categories.index'); Route::post('categories', [SolutionController::class, 'storeCategory'])->name('categories.store'); Route::match(['put', 'patch'], 'categories/{category}', [SolutionController::class, 'updateCategory'])->name('categories.update'); Route::post('categories/{category}/move-up', [SolutionController::class, 'moveCategory'])->defaults('direction', 'up')->name('categories.move-up'); Route::post('categories/{category}/move-down', [SolutionController::class, 'moveCategory'])->defaults('direction', 'down')->name('categories.move-down');
        Route::get('{solution}/edit', [SolutionController::class, 'edit'])->name('edit'); Route::match(['put', 'patch'], '{solution}', [SolutionController::class, 'update'])->name('update'); Route::post('{solution}/publish', [SolutionController::class, 'publish'])->name('publish'); Route::post('{solution}/schedule', [SolutionController::class, 'schedule'])->name('schedule'); Route::post('{solution}/unpublish', [SolutionController::class, 'unpublish'])->name('unpublish'); Route::post('{solution}/archive', [SolutionController::class, 'archive'])->name('archive'); Route::post('{solution}/restore', [SolutionController::class, 'restore'])->name('restore'); Route::post('{solution}/duplicate', [SolutionController::class, 'duplicate'])->name('duplicate'); Route::post('{solution}/move-up', [SolutionController::class, 'move'])->defaults('direction', 'up')->name('move-up'); Route::post('{solution}/move-down', [SolutionController::class, 'move'])->defaults('direction', 'down')->name('move-down'); Route::get('{solution}/preview', [SolutionController::class, 'preview'])->name('preview'); Route::post('{solution}/relations', [SolutionController::class, 'relations'])->name('relations');
        Route::post('{solution}/{type}', [SolutionChildController::class, 'store'])->where('type', 'features|benefits|capabilities|process-steps|use-cases')->name('children.store'); Route::match(['put', 'patch'], '{solution}/{type}/{child}', [SolutionChildController::class, 'update'])->where('type', 'features|benefits|capabilities|process-steps|use-cases')->name('children.update'); Route::delete('{solution}/{type}/{child}', [SolutionChildController::class, 'destroy'])->where('type', 'features|benefits|capabilities|process-steps|use-cases')->name('children.destroy'); Route::post('{solution}/{type}/{child}/move', [SolutionChildController::class, 'move'])->where('type', 'features|benefits|capabilities|process-steps|use-cases')->name('children.move');
    });
    Route::prefix('content/products')->name('admin.products.')->middleware('throttle:60,1')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index'); Route::get('create', [ProductController::class, 'create'])->name('create'); Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('categories', [ProductController::class, 'categories'])->name('categories.index'); Route::post('categories', [ProductController::class, 'storeCategory'])->name('categories.store'); Route::match(['put','patch'], 'categories/{category}', [ProductController::class, 'updateCategory'])->name('categories.update'); Route::post('categories/{category}/move-up', [ProductController::class, 'moveCategory'])->defaults('direction','up')->name('categories.move-up'); Route::post('categories/{category}/move-down', [ProductController::class, 'moveCategory'])->defaults('direction','down')->name('categories.move-down');
        Route::get('{product}/edit', [ProductController::class, 'edit'])->name('edit'); Route::match(['put','patch'], '{product}', [ProductController::class, 'update'])->name('update'); Route::post('{product}/publish', [ProductController::class, 'publish'])->name('publish'); Route::post('{product}/schedule', [ProductController::class, 'schedule'])->name('schedule'); Route::post('{product}/unpublish', [ProductController::class, 'unpublish'])->name('unpublish'); Route::post('{product}/archive', [ProductController::class, 'archive'])->name('archive'); Route::post('{product}/restore', [ProductController::class, 'restore'])->name('restore'); Route::post('{product}/duplicate', [ProductController::class, 'duplicate'])->name('duplicate'); Route::post('{product}/move-up', [ProductController::class, 'move'])->defaults('direction','up')->name('move-up'); Route::post('{product}/move-down', [ProductController::class, 'move'])->defaults('direction','down')->name('move-down'); Route::get('{product}/preview', [ProductController::class, 'preview'])->name('preview'); Route::post('{product}/relations', [ProductController::class, 'relations'])->name('relations');
        Route::post('{product}/feature-groups/{group}/features', [ProductChildController::class, 'storeFeature'])->name('features.store'); Route::delete('{product}/feature-groups/{group}/features/{feature}', [ProductChildController::class, 'destroyFeature'])->name('features.destroy');
        Route::post('{product}/{type}', [ProductChildController::class, 'store'])->where('type','editions|feature-groups|benefits|specifications|gallery|use-cases|integrations')->name('children.store'); Route::match(['put','patch'], '{product}/{type}/{child}', [ProductChildController::class, 'update'])->where('type','editions|feature-groups|benefits|specifications|gallery|use-cases|integrations')->name('children.update'); Route::delete('{product}/{type}/{child}', [ProductChildController::class, 'destroy'])->where('type','editions|feature-groups|benefits|specifications|gallery|use-cases|integrations')->name('children.destroy'); Route::post('{product}/{type}/{child}/move', [ProductChildController::class, 'move'])->where('type','editions|feature-groups|benefits|specifications|gallery|use-cases|integrations')->name('children.move');
    });

    Route::prefix('content/industries')->name('admin.industries.')->middleware('throttle:60,1')->group(function () {
        Route::get('/', [IndustryController::class, 'index'])->name('index'); Route::get('create', [IndustryController::class, 'create'])->name('create'); Route::post('/', [IndustryController::class, 'store'])->name('store');
        Route::get('categories', [IndustryController::class, 'categories'])->name('categories.index'); Route::post('categories', [IndustryController::class, 'storeCategory'])->name('categories.store'); Route::match(['put','patch'], 'categories/{category}', [IndustryController::class, 'updateCategory'])->name('categories.update'); Route::post('categories/{category}/move-up', [IndustryController::class, 'moveCategory'])->defaults('direction','up')->name('categories.move-up'); Route::post('categories/{category}/move-down', [IndustryController::class, 'moveCategory'])->defaults('direction','down')->name('categories.move-down');
        Route::get('{industry}/edit', [IndustryController::class, 'edit'])->name('edit'); Route::match(['put','patch'], '{industry}', [IndustryController::class, 'update'])->name('update'); Route::post('{industry}/publish', [IndustryController::class, 'publish'])->name('publish'); Route::post('{industry}/schedule', [IndustryController::class, 'schedule'])->name('schedule'); Route::post('{industry}/unpublish', [IndustryController::class, 'unpublish'])->name('unpublish'); Route::post('{industry}/archive', [IndustryController::class, 'archive'])->name('archive'); Route::post('{industry}/restore', [IndustryController::class, 'restore'])->name('restore'); Route::post('{industry}/duplicate', [IndustryController::class, 'duplicate'])->name('duplicate'); Route::post('{industry}/move-up', [IndustryController::class, 'move'])->defaults('direction','up')->name('move-up'); Route::post('{industry}/move-down', [IndustryController::class, 'move'])->defaults('direction','down')->name('move-down'); Route::get('{industry}/preview', [IndustryController::class, 'preview'])->name('preview'); Route::post('{industry}/relations', [IndustryController::class, 'relations'])->name('relations');
        Route::post('{industry}/{type}', [IndustryChildController::class, 'store'])->where('type','challenges|outcomes|needs|use-cases')->name('children.store'); Route::match(['put','patch'], '{industry}/{type}/{child}', [IndustryChildController::class, 'update'])->where('type','challenges|outcomes|needs|use-cases')->name('children.update'); Route::delete('{industry}/{type}/{child}', [IndustryChildController::class, 'destroy'])->where('type','challenges|outcomes|needs|use-cases')->name('children.destroy'); Route::post('{industry}/{type}/{child}/move', [IndustryChildController::class, 'move'])->where('type','challenges|outcomes|needs|use-cases')->name('children.move');
    });

    Route::prefix('content/capabilities')->name('admin.capabilities.')->middleware('throttle:60,1')->group(function () {
        Route::get('/', [CapabilityController::class, 'index'])->name('index'); Route::get('create', [CapabilityController::class, 'create'])->name('create'); Route::post('/', [CapabilityController::class, 'store'])->name('store');
        Route::get('categories', [CapabilityController::class, 'categories'])->name('categories.index'); Route::post('categories', [CapabilityController::class, 'storeCategory'])->name('categories.store'); Route::match(['put','patch'], 'categories/{category}', [CapabilityController::class, 'updateCategory'])->name('categories.update'); Route::post('categories/{category}/move-up', [CapabilityController::class, 'moveCategory'])->defaults('direction','up')->name('categories.move-up'); Route::post('categories/{category}/move-down', [CapabilityController::class, 'moveCategory'])->defaults('direction','down')->name('categories.move-down');
        Route::get('{capability}/edit', [CapabilityController::class, 'edit'])->name('edit'); Route::match(['put','patch'], '{capability}', [CapabilityController::class, 'update'])->name('update'); Route::post('{capability}/publish', [CapabilityController::class, 'publish'])->name('publish'); Route::post('{capability}/schedule', [CapabilityController::class, 'schedule'])->name('schedule'); Route::post('{capability}/unpublish', [CapabilityController::class, 'unpublish'])->name('unpublish'); Route::post('{capability}/archive', [CapabilityController::class, 'archive'])->name('archive'); Route::post('{capability}/restore', [CapabilityController::class, 'restore'])->name('restore'); Route::post('{capability}/duplicate', [CapabilityController::class, 'duplicate'])->name('duplicate'); Route::post('{capability}/move-up', [CapabilityController::class, 'move'])->defaults('direction','up')->name('move-up'); Route::post('{capability}/move-down', [CapabilityController::class, 'move'])->defaults('direction','down')->name('move-down'); Route::get('{capability}/preview', [CapabilityController::class, 'preview'])->name('preview'); Route::post('{capability}/relations', [CapabilityController::class, 'relations'])->name('relations');
        Route::post('{capability}/{type}', [CapabilityChildController::class, 'store'])->where('type','highlights|benefits|facts')->name('children.store'); Route::match(['put','patch'], '{capability}/{type}/{child}', [CapabilityChildController::class, 'update'])->where('type','highlights|benefits|facts')->name('children.update'); Route::delete('{capability}/{type}/{child}', [CapabilityChildController::class, 'destroy'])->where('type','highlights|benefits|facts')->name('children.destroy'); Route::post('{capability}/{type}/{child}/move', [CapabilityChildController::class, 'move'])->where('type','highlights|benefits|facts')->name('children.move');
    });

    Route::prefix('content/work-processes')->name('admin.work-processes.')->middleware('throttle:60,1')->group(function () {
        Route::get('/', [WorkProcessController::class, 'index'])->name('index'); Route::get('create', [WorkProcessController::class, 'create'])->name('create'); Route::post('/', [WorkProcessController::class, 'store'])->name('store');
        Route::get('{workProcess}/edit', [WorkProcessController::class, 'edit'])->name('edit'); Route::match(['put','patch'], '{workProcess}', [WorkProcessController::class, 'update'])->name('update'); Route::post('{workProcess}/publish', [WorkProcessController::class, 'publish'])->name('publish'); Route::post('{workProcess}/schedule', [WorkProcessController::class, 'schedule'])->name('schedule'); Route::post('{workProcess}/unpublish', [WorkProcessController::class, 'unpublish'])->name('unpublish'); Route::post('{workProcess}/archive', [WorkProcessController::class, 'archive'])->name('archive'); Route::post('{workProcess}/restore', [WorkProcessController::class, 'restore'])->name('restore'); Route::post('{workProcess}/duplicate', [WorkProcessController::class, 'duplicate'])->name('duplicate'); Route::post('{workProcess}/move-up', [WorkProcessController::class, 'move'])->defaults('direction','up')->name('move-up'); Route::post('{workProcess}/move-down', [WorkProcessController::class, 'move'])->defaults('direction','down')->name('move-down'); Route::get('{workProcess}/preview', [WorkProcessController::class, 'preview'])->name('preview'); Route::post('{workProcess}/relations', [WorkProcessController::class, 'relations'])->name('relations');
        Route::post('{workProcess}/stages', [WorkProcessChildController::class, 'storeStage'])->name('stages.store'); Route::delete('{workProcess}/stages/{stage}', [WorkProcessChildController::class, 'destroyStage'])->name('stages.destroy'); Route::post('{workProcess}/stages/{stage}/move', [WorkProcessChildController::class, 'moveStage'])->name('stages.move'); Route::post('{workProcess}/stages/{stage}/deliverables', [WorkProcessChildController::class, 'storeDeliverable'])->name('deliverables.store'); Route::delete('{workProcess}/stages/{stage}/deliverables/{deliverable}', [WorkProcessChildController::class, 'destroyDeliverable'])->name('deliverables.destroy'); Route::post('{workProcess}/stages/{stage}/deliverables/{deliverable}/move', [WorkProcessChildController::class, 'moveDeliverable'])->name('deliverables.move');
    });



    Route::prefix('content/articles')->name('admin.articles.')->middleware('throttle:60,1')->controller(ArticleController::class)->group(function () {
        Route::get('/', 'index')->name('index'); Route::get('create', 'create')->name('create'); Route::post('/', 'store')->name('store'); Route::get('categories', 'categories')->name('categories.index'); Route::post('categories', 'storeArticleCategory')->name('categories.store');
        Route::get('{article}/edit', 'edit')->name('edit'); Route::match(['put','patch'], '{article}', 'update')->name('update');
        foreach (['publish','schedule','unpublish','archive','restore','duplicate'] as $action) { Route::post("{article}/{$action}", $action)->name($action); }
        Route::post('{article}/move-up', 'move')->defaults('direction','up')->name('move-up'); Route::post('{article}/move-down', 'move')->defaults('direction','down')->name('move-down'); Route::get('{article}/preview', 'preview')->name('preview');
    });
    Route::prefix('content/case-studies')->name('admin.case-studies.')->middleware('throttle:60,1')->controller(CaseStudyController::class)->group(function () {
        Route::get('/', 'index')->name('index'); Route::get('create', 'create')->name('create'); Route::post('/', 'store')->name('store'); Route::get('{caseStudy}/edit', 'edit')->name('edit'); Route::match(['put','patch'], '{caseStudy}', 'update')->name('update');
        foreach (['publish','schedule','unpublish','archive','restore','duplicate'] as $action) { Route::post("{caseStudy}/{$action}", $action)->name($action); }
        Route::post('{caseStudy}/{type}', [CaseStudyChildController::class, 'store'])->where('type','metrics|highlights')->name('children.store'); Route::delete('{caseStudy}/{type}/{child}', [CaseStudyChildController::class, 'destroy'])->where('type','metrics|highlights')->name('children.destroy');
        Route::post('{caseStudy}/move-up', 'move')->defaults('direction','up')->name('move-up'); Route::post('{caseStudy}/move-down', 'move')->defaults('direction','down')->name('move-down'); Route::get('{caseStudy}/preview', 'preview')->name('preview');
    });
    Route::prefix('content/faqs')->name('admin.faqs.')->middleware('throttle:60,1')->controller(FaqController::class)->group(function () {
        Route::get('/', 'index')->name('index'); Route::get('create', 'create')->name('create'); Route::post('/', 'store')->name('store'); Route::get('groups', 'groups')->name('groups.index'); Route::post('groups', 'storeFaqGroup')->name('groups.store'); Route::get('{faq}/edit', 'edit')->name('edit'); Route::match(['put','patch'], '{faq}', 'update')->name('update');
        foreach (['publish','schedule','unpublish','archive','restore','duplicate'] as $action) { Route::post("{faq}/{$action}", $action)->name($action); }
        Route::post('{faq}/move-up', 'move')->defaults('direction','up')->name('move-up'); Route::post('{faq}/move-down', 'move')->defaults('direction','down')->name('move-down'); Route::get('{faq}/preview', 'preview')->name('preview');
    });

    foreach ([
        'clients' => ClientController::class,
        'testimonials' => TestimonialController::class,
        'statistics' => StatisticController::class,
    ] as $prefix => $controller) {
        Route::prefix("content/{$prefix}")->name("admin.{$prefix}.")->middleware('throttle:60,1')->controller($controller)->group(function () use ($prefix) {
            $parameter = $prefix === 'statistics' ? 'statistic' : rtrim($prefix, 's');
            Route::get('/', 'index')->name('index'); Route::get('create', 'create')->name('create'); Route::post('/', 'store')->name('store');
            Route::get("{{$parameter}}/edit", 'edit')->name('edit'); Route::match(['put', 'patch'], "{{$parameter}}", 'update')->name('update');
            foreach (['publish', 'schedule', 'unpublish', 'archive', 'restore', 'duplicate'] as $action) { Route::post("{{$parameter}}/{$action}", $action)->name($action); }
            Route::post("{{$parameter}}/move-up", 'move')->defaults('direction', 'up')->name('move-up'); Route::post("{{$parameter}}/move-down", 'move')->defaults('direction', 'down')->name('move-down'); Route::get("{{$parameter}}/preview", 'preview')->name('preview');
            if ($prefix === 'clients') { Route::post('{client}/relations', 'relations')->name('relations'); }
        });
    }


});

Route::middleware(['auth', 'administrator.active'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';

Route::get('{slug}', PublicPageController::class)->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')->name('pages.show');
