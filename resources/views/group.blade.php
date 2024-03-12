@extends('layout')
<title>Group</title>
@section('content')
<div class="page-content page-container container-fluid" id="page-content">
    <div class="row">
        <div class="col-md-3 border-right">
            <form id="assistantForm"
                action="{{ isset($group) ? route('updateGroup', ['groupId'=>$group['id']]) : route('createGroup')}}"
                method="post" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="groupName">Name</label>
                    <input type="text" class="form-control" name="groupName" id="grouptName"
                        placeholder="Type Group Name"
                        value="{{old('groupName', $group['name'] ?? '')}}" autocomplete="off" required>
                </div>
                <div class="form-group">
                    <label for="selectedAssistant">Assistant</label>
                    <select class="form-select" name="selectedAssistant" id="selectedAssistant" aria-label="Default select assistant" disabled>
                        <option selected value="">Select Assistant</option>
                      </select>
                </div>
                {{-- <button type="submit" class="btn btn-warning btn-flat">Save</button> --}}
                <button type="submit" class="btn btn-{{ isset($assistant) ? 'info' : 'warning' }} btn-flat">
                    {{ isset($assistant) ? 'Update' : 'Save' }}
                </button>
            </form>
        </div>
        <div class="col-md-9">
            <div class="row align-items-center">
                <h2 class="col-md-9">List of Groups</h2>
                @if(isset($assistant))
                    <span class="col-md-3"><a href="#"><button class="btn btn-success">Add Group</button></a></span>
                @endif
            </div>
            <div>
                <table class="table table-hover">
                    <thead class="text-center">
                        <tr>
                            <th>Group Name</th>
                            {{-- <th scope="col" class="col-md-6">Instructions</th> --}}
                            <th>Action</th>
                        </tr>
                    </thead>
                    {{-- @if (Auth::check()) --}}
                        @if (isset($groups))
                            @foreach ($groups as $group)
                                <tbody>
                                    <tr>
                                        <td scope="col" class="col-md-8">{{ $group['name'] }}</td>
                                        <td scope="col">
                                            @if($group['user_id'] === Auth::user()->id )
                                                <a href="{{route('getGroup', ['groupId'=>$group['id']])}}">
                                                    <button class="btn btn-info">Edit</button>
                                                </a>
                                            @endif
                                            <a href="{{route('groupChat', ['groupId'=>$group['id']])}}">
                                                <button class="btn btn-success">Chat</button>
                                            </a>
                                            {{-- <a href="{{route('deleteAssistant', ['assistantId'=> $assistant['id']])}}"><button class="btn btn-danger">Delete</button></a> --}}
                                        </td>
                                    </tr>
                                </tbody>
                            @endforeach
                        @else
                            <tbody>
                                <tr>
                                    <td scope="col" class="col-md-12 text-center">No Group Found!</td>
                                </tr>
                            </tbody>
                        @endif
                    {{-- @endif --}}
                </table>
            </div>
        </div>
    </div>
    {{-- </div> --}}
</div>
@endsection
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<script>
    $(document).ready(function () {
        $.ajax({
            url: "{{ route('selectAssistant') }}",
            type: "GET",
            dataType: "json",
            success: function (data) {
                // Update the options in the select dropdown
                $.each(data, function (index, assistant) {
                    $('#selectedAssistant').append('<option value="' + assistant.assistantId + '">' + assistant.name + '</option>');
                });

                $('#selectedAssistant').prop('disabled', false);
            }
        });
    });
</script>