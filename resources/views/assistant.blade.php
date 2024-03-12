{{-- <!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge"> --}}
    @extends('layout')

    {{-- <link href=" {{ asset('assets/css/bootstrap.min.css') }} " rel="stylesheet" />
    <link href=" {{ asset('assets/js/bootstrap.bundle.min.js') }} " rel="stylesheet" />
    <link href=" {{ asset('assets/js/jquery.min.js') }} " rel="stylesheet" />
    <link href=" {{ asset('assets/css/font-awesome.css') }} " rel="stylesheet" /> --}}
    
    {{-- <link href="#" rel="stylesheet" /> --}}
    <title>Assistant</title>
    
    {{-- <style></style>
    </head>
    
    <body> --}}
        @section('content')
        
    <div class="page-content page-container container-fluid" id="page-content">
        {{-- <div class="row container d-flex justify-content-center"> --}}
        <div class="row">
            {{-- {{$assistant['id']}} --}}
            <div class="col-md-4 border-right">
                <form id="assistantForm"
                    action=" {{ isset($assistant) ? route('updateAssistant', ['assistantId' => $assistant['id']]) : route('createAssistant') }} "
                    method="post" enctype="multipart/form-data">
                    {{-- <form id="assistantForm" action=" {{route('createAssistant')}} " method="post"> --}}
                    @csrf
                    <div class="form-group">
                        <label for="assistantName">Name</label>
                        <input type="text" class="form-control" name="assistantName" id="assistantName"
                            placeholder="Type Assistant Name"
                            value="{{ old('assistantName', $assistant['name'] ?? '') }}" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="assistantInstruction">Instrctions</label>
                        <textarea class="form-control" name="assistantInstruction" id="assistantInstruction" rows="5"
                            placeholder="Type Instrutions for Assistant">{{ isset($assistant) ? $assistant['instructions'] : null }}</textarea>
                    </div>
                    <div class="input-group mb-3">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="uploadFile" name="uploadFile"
                                onchange="updateFileName()">
                            <label class="custom-file-label" for="inputGroupFile02"
                                aria-describedby="inputGroupFileAddon02">Choose file</label>
                        </div>
                    </div>
                    <!-- Display existing files for update -->
                    @if(isset($assistant))
                    <div class="form-group">
                        <label for="existingFiles">Attached Files</label>
                        <ul>
                            @if (isset($files) && count($files) > 0)
                                @foreach ($files as $file)
                                    <li>{{ $file['filename'] }} <span class="float-md-right pr-md-5"><a href="{{route('deleteFile',['assistantId' => $assistant['id'],'fileId'=>$file['id']])}}">x</a></span> </li>
                                @endforeach
                            @else
                                <li>No Files Attached to Assistant!</li>
                            @endif
                        </ul>
                    </div>
                    @endif
                    {{-- <button type="submit" class="btn btn-warning btn-flat">Save</button> --}}
                    <button type="submit" class="btn btn-{{ isset($assistant) ? 'info' : 'warning' }} btn-flat">
                        {{ isset($assistant) ? 'Update' : 'Save' }}
                    </button>
                </form>
            </div>
            <div class="col-md-8">
                <div class="row align-items-center">
                    <h2 class="col-md-9">List of Assistants</h2>
                    @if(isset($assistant))
                        <span class="col-md-3"><a href="{{route('listUserAssistants')}}"><button class="btn btn-success">Add assistant</button></a></span>
                    @endif
                </div>
                <div>
                    <table class="table table-hover">
                        <thead class="text-center">
                            <tr>
                                <th scope="col" class="col-md-3">Assistant Name</th>
                                <th scope="col" class="col-md-6">Instructions</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        @if (Auth::check())
                            @if (isset($assistants) && count($assistants) > 0)
                                @foreach ($assistants as $assistant)
                                    <tbody>
                                        <tr>
                                            <td>{{ $assistant['name'] }}</td>
                                            <td>{{ $assistant['instructions'] }}</td>
                                            <td>
                                                <a href="{{ route('retrieveAssistant', ['assistantId' => $assistant['id']]) }}">
                                                    <button class="btn btn-info">Edit</button>
                                                </a>
                                                <a href="{{ route('getLastThread', ['assistantId' => $assistant['id']]) }}">
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
                                        <td></td>
                                        <td class="text-center">No Assistants Found!</td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            @endif
                        @endif
                    </table>
                </div>
            </div>
        </div>
        {{-- </div> --}}
    </div>

    @endsection
    {{-- <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script> --}}
    <script>
        function updateFileName() {
            var fileName = document.getElementById('uploadFile').files[0].name;
            console.log(fileName);
            document.querySelector('.custom-file-label').innerText = fileName;
        }
    </script>
{{-- </body>

</html> --}}
