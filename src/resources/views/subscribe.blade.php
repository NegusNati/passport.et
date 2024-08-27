<x-layout>


    <div class="pb-25 p-8 pt-10 max-w-[990px] m-auto border border-blue-600 rounded-lg ">
        <div class="container">
            <h1>Choose Your Plan</h1>
            <form action="/subscribe" method="POST">
                @csrf
                <div>
                    <input type="radio" id="free" name="plan" value="free">
                    <label for="free">Free Plan</label>
                </div>
                <div>
                    <input type="radio" id="premium" name="plan" value="premium">
                    <label for="premium">Premium Plan</label>
                </div>
                <button type="submit">Subscribe</button>
            </form>
        </div>
    </div>
</x-layout>
