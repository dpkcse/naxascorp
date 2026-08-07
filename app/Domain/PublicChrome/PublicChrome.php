<?php
namespace App\Domain\PublicChrome;
use App\Models\{BrandingSetting,FooterSetting,HeaderSetting,NavigationMenu,WebsiteSetting}; use Illuminate\Support\Facades\Cache; use Illuminate\Support\Facades\Schema; use Throwable;
class PublicChrome
{
    private ?array $resolved=null;
    public function data(): array
    {
        return $this->resolved ??= ['branding'=>$this->branding(),'header'=>$this->header(),'navigation'=>$this->navigation(),'footer'=>$this->footer()];
    }
    private function cached(string $key, callable $loader, array $fallback, int $minutes = 30): array
    {
        try { return Cache::remember($key, now()->addMinutes($minutes), $loader); } catch (Throwable) { try { return $loader(); } catch (Throwable) { return $fallback; } }
    }
    private function branding(): array
    {
        $fallback=['logo'=>null,'logo_dark'=>null,'favicon'=>null,'alt'=>null,'mark'=>'N','primary'=>'#2563eb','secondary'=>'#081a33','accent'=>'#0ea5e9','attribution'=>true];
        return $this->cached(PublicChromeCache::BRANDING, function () use ($fallback) { if (! Schema::hasTable('branding_settings')) return $fallback; $v=BrandingSetting::query()->first(); if (! $v) return $fallback; return ['logo'=>PublicAssetPath::isSafe($v->site_logo_path) ? $v->site_logo_path : null,'logo_dark'=>PublicAssetPath::isSafe($v->site_logo_dark_path) ? $v->site_logo_dark_path : null,'favicon'=>PublicAssetPath::isSafe($v->favicon_path,true) ? $v->favicon_path : null,'alt'=>$v->logo_alt_text,'mark'=>$v->brand_mark_text ?: 'N','primary'=>$v->primary_color ?: '#2563eb','secondary'=>$v->secondary_color ?: '#081a33','accent'=>$v->accent_color ?: '#0ea5e9','attribution'=>(bool)$v->show_product_attribution]; },$fallback);
    }
    private function header(): array
    {
        $fallback=['show_top_bar'=>true,'sticky'=>true,'show_site_name'=>true,'cta'=>null,'message'=>null,'support'=>null,'careers'=>null];
        return $this->cached(PublicChromeCache::HEADER,function() use($fallback){ if(!Schema::hasTable('header_settings'))return $fallback;$v=HeaderSetting::query()->first();if(!$v)return $fallback; return ['show_top_bar'=>(bool)$v->show_top_bar,'sticky'=>(bool)$v->sticky_header,'show_site_name'=>(bool)$v->show_site_name,'cta'=>$v->show_primary_cta ? $this->link($v->primary_cta_label,$v->primary_cta_url,$v->primary_cta_target) : null,'message'=>$v->top_bar_message,'support'=>$this->link($v->support_label,$v->support_url),'careers'=>$this->link($v->careers_label,$v->careers_url)];},$fallback);
    }
    private function navigation(): array
    {
        $fallback=[['id'=>'fallback-home','label'=>'Home','href'=>route('home'),'target'=>'_self','external'=>false,'route'=>'home','disabled'=>false,'featured'=>false,'mega'=>false,'description'=>null,'badge'=>null,'children'=>[]],['id'=>'fallback-company','label'=>'Company','href'=>null,'target'=>'_self','external'=>false,'route'=>null,'disabled'=>true,'featured'=>false,'mega'=>false,'description'=>null,'badge'=>null,'children'=>[]]];
        return $this->cached(PublicChromeCache::PRIMARY,function() use($fallback){if(!Schema::hasTable('navigation_menus'))return $fallback;$menu=NavigationMenu::query()->where('location','primary')->where('is_active',true)->with(['items'=>fn($q)=>$q->with(['page','solution','product','industry','capability','workProcess'])->where('is_active',true)->orderBy('display_order')->orderBy('id')->limit(100)])->first();if(!$menu)return $fallback;$items=$menu->items->groupBy('parent_id'); return $this->tree($items,null,1,3);},$fallback,1);
    }
    private function tree($groups, ?int $parent, int $depth, int $max): array
    {
        if($depth>$max)return []; return $groups->get($parent,collect())->take($parent===null?12:20)->map(function($item) use($groups,$depth,$max){$pageVisible=$item->link_type==='page'&&$item->page&&$item->page->isPubliclyVisible();$solutionVisible=$item->link_type==='solution'&&$item->solution&&$item->solution->isPubliclyVisible();$productVisible=$item->link_type==='product'&&$item->product&&$item->product->isPubliclyVisible();$industryVisible=$item->link_type==='industry'&&$item->industry&&$item->industry->isPubliclyVisible();$capabilityVisible=$item->link_type==='capability'&&$item->capability&&$item->capability->isPubliclyVisible();$processVisible=$item->link_type==='work_process'&&$item->workProcess&&$item->workProcess->isPubliclyVisible();$href=$pageVisible?route('pages.show',$item->page->slug):($solutionVisible?route('solutions.show',$item->solution->slug):($productVisible?route('products.show',$item->product->slug):($industryVisible?route('industries.show',$item->industry->slug):($capabilityVisible?route('capabilities.show',$item->capability->slug):($processVisible?route('work-processes.show',$item->workProcess->slug):PublicLink::href($item->link_type,$item->route_name,$item->url))))));return ['id'=>'nav-'.$item->id,'label'=>$item->label,'href'=>$href,'target'=>$item->target,'external'=>PublicLink::isExternal($item->url),'route'=>$item->route_name,'disabled'=>$item->link_type==='disabled'||(in_array($item->link_type,['page','solution','product','industry','capability','work_process'],true)&&!$pageVisible&&!$solutionVisible&&!$productVisible&&!$industryVisible&&!$capabilityVisible&&!$processVisible),'featured'=>(bool)$item->is_featured,'mega'=>(bool)$item->opens_mega_menu,'description'=>$item->description,'badge'=>$item->badge_text,'children'=>$this->tree($groups,$item->id,$depth+1,$max)];})->values()->all();
    }
    private function footer(): array
    {
        $fallback=['configured'=>false,'description'=>null,'contact'=>true,'social'=>[],'columns'=>[],'legal'=>[],'copyright'=>null,'developer'=>'Developed by Naxas Innovations Limited','developer_url'=>null,'attribution'=>true];
        return $this->cached(PublicChromeCache::FOOTER,function() use($fallback){if(!Schema::hasTable('footer_settings'))return $fallback;$v=FooterSetting::query()->with(['columns'=>fn($q)=>$q->where('is_active',true)->orderBy('display_order')->with(['links'=>fn($l)=>$l->where('is_active',true)->orderBy('display_order')]),'socialLinks'=>fn($q)=>$q->where('is_active',true)->orderBy('display_order')])->first();if(!$v)return $fallback;$columns=$v->columns->map(fn($c)=>['title'=>$c->title,'links'=>$c->links->map(fn($l)=>$this->publicLink($l))->filter()->values()->all()])->filter(fn($c)=>count($c['links'])>0)->values()->all();$social=$v->show_social_links?$v->socialLinks->map(fn($s)=>PublicLink::isSafeUrl($s->url,false)?['label'=>$s->label,'platform'=>$s->platform,'icon'=>$s->icon,'href'=>$s->url,'external'=>true]:null)->filter()->values()->all():[];return ['configured'=>true,'description'=>$v->short_description,'contact'=>(bool)$v->show_contact_details,'social'=>$social,'columns'=>$columns,'legal'=>$v->show_legal_links?$this->menuLinks('legal'):[],'copyright'=>$v->copyright_text,'developer'=>$v->developed_by_text,'developer_url'=>PublicLink::isSafeUrl($v->developed_by_url)?$v->developed_by_url:null,'attribution'=>true];},$fallback);
    }
    private function menuLinks(string $location):array { $menu=NavigationMenu::query()->where('location',$location)->where('is_active',true)->with(['items'=>fn($q)=>$q->whereNull('parent_id')->where('is_active',true)->orderBy('display_order')->limit(20)])->first(); return $menu?$menu->items->map(fn($i)=>$this->publicLink($i))->filter()->values()->all():[]; }
    private function publicLink($item):?array{$href=PublicLink::href($item->link_type,$item->route_name,$item->url);if(!$href&&$item->link_type!=='disabled')return null;return ['label'=>$item->label,'href'=>$href,'target'=>$item->target,'external'=>PublicLink::isExternal($item->url),'disabled'=>$item->link_type==='disabled'];}
    private function link(?string $label,?string $url,string $target='_self'):?array{return $label&&PublicLink::isSafeUrl($url)?['label'=>$label,'href'=>$url,'target'=>in_array($target,['_self','_blank'],true)?$target:'_self','external'=>PublicLink::isExternal($url)]:null;}
}
