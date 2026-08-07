<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\BelongsTo; class CaseStudyHighlight extends Model { protected $fillable=['title','description','icon','display_order','is_active']; protected function casts():array{return ['is_active'=>'boolean'];} public function caseStudy():BelongsTo{return $this->belongsTo(CaseStudy::class);} }
