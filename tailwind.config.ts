import type { Config } from 'tailwindcss'
import forms from '@tailwindcss/forms'
import typography from '@tailwindcss/typography'

export default {
  content: [
    './resources/**/*.blade.php',
    './resources/views/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
    './src/**/*.php',
    './src/**/**/*.php',
    './src/**/**/**/*.php',
  ],
  plugins: [
    forms,
    typography,
  ],
} satisfies Config