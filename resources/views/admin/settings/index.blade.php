@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    {{ __('Settings') }}
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    <form method="post">
                        @csrf
                        <table class="table" >                        
                            <tbody>                        
                            @foreach($rows as $row)
                            <tr>
                                <td>{{$row->label}}</td>
                                <td>
                                    <input type='text' class="form-control"  
                                        name='val[{{$row->id}}]' value='{{$row->val}}' />
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                            <tfoot>                                
                                <th colspan="2" >
                                    <button type="submit" class="btn btn-success" >Update</button>
                                </th>
                            </tfoot>
                        </table>           
                    </form>         
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
