@props(['message'])
<x-layout>

    <div class="pb-25 p-8 pt-10 max-w-[990px] m-auto border border-blue-600 rounded-lg ">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Rate Limit Exceeded</div>

                <div class="card-body">
                    <p>{{ $message }}</p>
                    <a href="{{ url('/') }}" class="btn btn-primary">Go Back to Welcome Page</a>
                </div>
            </div>
        </div>
    </div>
</div>
    </div>
</x-layout>
