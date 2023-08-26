<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadStage extends Model
{
    protected $fillable = [
        'name',
        'pipeline_id',
        'created_by',
        'order',
    ];

    public function lead()
    {
        //  return Lead::select('leads.*')->join('user_leads', 'user_leads.lead_id', '=', 'leads.id')->where('user_leads.user_id', '=', \Auth::user()->id)->where('leads.stage_id', '=', $this->id)->orderBy('leads.order')->get();
 if(\Auth::user()->type == 'company' || \Auth::user()->type == 'super admin' ||  \Auth::user()->type == 'Admin' )
        {
           
            return Lead::select('leads.*')->join('user_leads', 'user_leads.lead_id', '=', 'leads.id')->where('leads.stage_id', '=', $this->id)->GroupBy('leads.name')->orderBy('leads.id')->get();
  }
  else{
         return Lead::select('leads.*')->join('user_leads', 'user_leads.lead_id', '=', 'leads.id')->where('user_leads.user_id', '=', \Auth::user()->id)->where('leads.stage_id', '=', $this->id)->GroupBy('leads.name')->orderBy('leads.id')->get();
  }
    }
}