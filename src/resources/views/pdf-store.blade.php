{{-- <html>

<head>

</head>
<body>

<div class="flex flex-col items-center justify-center h-screen">
    <div class="flex flex-col items-center justify-center w-full h-full">
        <div class="flex flex-col items-center justify-center w-full h-full">
            <h1 class="text-4xl font-bold text-center">PDF Store</h1>
            <div class="flex flex-col items-center justify-center w-full h-full">
                <form action="/pdf-to-sqlite" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="flex flex-col items-center justify-center w-full h-full">
                        <div class="flex flex-col items-center justify-center w-full h-full">

                            {{ csrf_field() }}
                            <div class="flex flex-col items-center justify-center w-full h-full">

                                <!-- Upload PDF -->
                                <div class="flex flex-col items-center justify-center w-full h-full">
                                    <label for="pdf_file" class="text-lg font-bold">Upload PDF</label>
                                    <input type="file" name="pdf_file" id="pdf_file" class="w-full h-full">
                                </div>
                                <div class="flex flex-col items-center justify-center w-full h-full">
                                    <label for="date" class="text-lg font-bold">Published Date</label>
                                    <input type="date" name="date" id="date" class="w-full h-full">

                                </div>


                                <div class="flex flex-col items-center justify-center w-full h-full">
                                    <button type="submit"
                                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                        Upload PDF
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>

</html> --}}

<x-layout>

    <div class="pb-25 p-8 pt-10 max-w-[990px] m-auto border border-blue-600 rounded-lg ">
        <x-header >Publish PDF To SQLite DB </x-header>
        <x-forms.form action="/pdf-to-sqlite" method="POST" enctype="multipart/form-data" class="mt-6">

            <x-forms.input name="pdf_file" id="pdf_file" label="Upload PDF" type="file" />
            <x-forms.input name="date" label="Published Date" type="date" />
            <x-forms.input name="location"  label="Location"  placeholder="Addis Ababa" />
            <x-forms.input name="linesToSkip"  label="Word To Skip After"  placeholder="Request No" />

            <x-forms.button>Publish PDF To SQLite</x-forms.button>

        </x-forms.form>


    </div>
</x-layout>
