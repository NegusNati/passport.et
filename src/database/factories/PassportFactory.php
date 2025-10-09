<?php

namespace Database\Factories;

use App\Domain\Passport\Models\Passport;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Domain\Passport\Models\Passport>
 */
class PassportFactory extends Factory
{
    protected $model = Passport::class;

    public function definition(): array
    {
        $first = $this->faker->firstName();
        $middle = $this->faker->firstName();
        $last = $this->faker->lastName();

        return [
            'no' => $this->faker->numberBetween(1, 9999),
            'firstName' => $first,
            'middleName' => $middle,
            'lastName' => $last,
            'requestNumber' => Str::upper($this->faker->bothify('??#####')),
            'location' => $this->faker->city(),
            'dateOfPublish' => $this->faker->date(),
        ];
    }
}
