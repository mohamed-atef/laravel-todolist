@extends('layouts.default')

@section('content')

{{--open tasks--}}
<div class="panel panel-primary">
    <div class="panel-heading">Open tasks</div>
    <div class="panel-body">
        <table class="table table-hover">
            <tbody id="open-tasks">
                <tr>
                    <td clospan="3">todo</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{--completed tasks--}}
<div class="panel panel-primary">
    <div class="panel-heading">Completed tasks</div>
    <div class="panel-body">
        <table class="table table-hover">
            <tbody id="completed-tasks">
                <tr>
                    <td clospan="3">done</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@stop
