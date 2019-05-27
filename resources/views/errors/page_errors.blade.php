@extends('layouts.antui')
@section('content')
        <div class="am-message result">
            <i class="am-icon result error"></i>
            <div class="am-message-main">失败</div>
            <div class="am-message-sub">{{$message}}</div>
        </div>
@endsection