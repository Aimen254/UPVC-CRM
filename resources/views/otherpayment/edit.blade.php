{{Form::model($otherpayment,array('route' => array('otherpayment.update', $otherpayment->id), 'method' => 'PUT')) }}
<div class="modal-body">

<div class="card-body p-0">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('title', __('Title')) }}
                {{ Form::text('title',null, array('class' => 'form-control ','required'=>'required')) }}
            </div>
        </div>
    </div>
    <div class="row">
         <div class="form-group col-md-12">
            {{ Form::label('inc_for', __('Incement For'),['class'=>'form-label']) }}
            {{ Form::text('inc_for',null, array('class' => 'form-control ')) }}
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('type', __('Type'), ['class' => 'form-label']) }}
                {{ Form::select('type', $otherpaytypes, null, ['class' => 'form-control select amount_type', 'required' => 'required']) }}
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('amount', __('Amount'),['class'=>'form-label amount_label']) }}
                {{ Form::number('amount',null, array('class' => 'form-control ','required'=>'required','step'=>'0.01')) }}
            </div>
        </div>
    </div>
</div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn  btn-primary">
</div>

{{Form::close()}}


