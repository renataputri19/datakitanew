import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/Monalisa/**/*.php',
        './resources/views/filament/monalisa/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
}
