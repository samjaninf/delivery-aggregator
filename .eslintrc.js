module.exports = {
  parser: "babel-eslint",
  extends: ["airbnb", "prettier"],
  plugins: ["react", "jsx-a11y", "import"],
  env: {
    browser: true,
    jest: true
  },
  rules: {
    "react/jsx-filename-extension": [1, { extensions: [".js", ".jsx"] }],
    "react/prop-types": [1, { skipUndeclared: true }]
  },
  globals: {
    "__DEV__": true
  }
};
