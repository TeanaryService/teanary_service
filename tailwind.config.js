/** @type {import('tailwindcss').Config} */
export default {
  presets: [preset],
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
  ],
  theme: {
    extend: {
      colors: {
        // 中国茶文化主题色彩
        'tea': {
          50: '#f0f9f0',
          100: '#dcf2dc',
          200: '#bce5bc',
          300: '#8dd18d',
          400: '#5bb85b',
          500: '#3a9d3a', // 主茶绿色
          600: '#2d7d2d',
          700: '#256325',
          800: '#205020',
          900: '#1c421c',
        },
        'bamboo': {
          50: '#f7f8f6',
          100: '#eef0eb',
          200: '#dde2d5',
          300: '#c4cdb8',
          400: '#a5b395',
          500: '#8a9a7a', // 竹绿色
          600: '#6d7a5f',
          700: '#58624c',
          800: '#4a5040',
          900: '#3f4336',
        },
        'ceramic': {
          50: '#faf9f7',
          100: '#f4f1ec',
          200: '#e8e1d6',
          300: '#d9cebc',
          400: '#c7b79e',
          500: '#b8a585', // 陶瓷色
          600: '#a6936f',
          700: '#8b7a5c',
          800: '#72634c',
          900: '#5d513f',
        },
        'ink': {
          50: '#f8f8f8',
          100: '#f0f0f0',
          200: '#e4e4e4',
          300: '#d1d1d1',
          400: '#b4b4b4',
          500: '#9a9a9a',
          600: '#818181',
          700: '#6a6a6a',
          800: '#5a5a5a',
          900: '#4a4a4a',
          950: '#262626', // 墨色
        }
      },
      fontFamily: {
        'chinese': ['PingFang SC', 'Hiragino Sans GB', 'Microsoft YaHei', 'WenQuanYi Micro Hei', 'sans-serif'],
      },
      backgroundImage: {
        'tea-pattern': "url('data:image/svg+xml,%3Csvg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"none\" fill-rule=\"evenodd\"%3E%3Cg fill=\"%23f0f9f0\" fill-opacity=\"0.1\"%3E%3Cpath d=\"M30 30c0-11.046-8.954-20-20-20s-20 8.954-20 20 8.954 20 20 20 20-8.954 20-20zm-20 0c0-5.523 4.477-10 10-10s10 4.477 10 10-4.477 10-10 10-10-4.477-10-10z\"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')",
        'bamboo-pattern': "url('data:image/svg+xml,%3Csvg width=\"40\" height=\"40\" viewBox=\"0 0 40 40\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"%23f7f8f6\" fill-opacity=\"0.1\"%3E%3Cpath d=\"M20 20c0-5.523-4.477-10-10-10S0 14.477 0 20s4.477 10 10 10 10-4.477 10-10z\"/%3E%3C/g%3E%3C/svg%3E')",
      }
    },
  },
  plugins: [
    require('@tailwindcss/typography'),
    require('@tailwindcss/line-clamp'),
  ],
}