<x-layouts.email>
    <x-slot name="preview">
        {{ __('Welcome to :app!', ['app' => config('app.name')]) }}
    </x-slot>

    <tr>
        <td class="sm-px-6" style="border-radius: 4px; padding: 48px; font-size: 16px; color: #334155; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05)" bgcolor="#ffffff">
            <h1 class="sm-leading-8" style="margin: 0 0 24px; font-size: 24px; font-weight: 600; color: #000">
                {{ __('Welcome to :app!', ['app' => config('app.name')]) }}
            </h1>
            <p style="margin: 0; line-height: 24px">
                {{ __('Hi :name, we\'re thrilled to have you on board!', ['name' => $user->name]) }}
            </p>

            <p style="margin-top: 16px; line-height: 24px">
                {{ __(':app is here to help you grow, stay organised, and make the most of every opportunity. Your account is all set up and ready to go.', ['app' => config('app.name')]) }}
            </p>

            <div style="text-align: center;">
                <a href="{{ route('verification.notice') }}" style="margin-top: 24px; margin-bottom: 24px; display: inline-block; border-radius: 16px; background-color: {{ config('app.email_color_tint') }}; padding: 8px 24px; font-size: 20px; color: #fff; text-decoration-line: none">
                    {{ __('Get Started') }}
                </a>
            </div>

            <div role="separator" style="background-color: #e2e8f0; height: 1px; line-height: 1px; margin: 32px 0;">&zwj;</div>

            <p style="margin: 0; line-height: 24px">
                {{ __('If you have any questions or need help getting started, our support team is always happy to assist. Reach us at') }}
                <a href="mailto:{{ config('app.support_email') }}">{{ config('app.support_email') }}</a>.
            </p>

            <p style="margin-top: 24px; line-height: 24px">
                {{ __('Sincerely,') }}<br>
                {{ config('app.name') }} {{ __('Team') }}
            </p>
        </td>
    </tr>
</x-layouts.email>
