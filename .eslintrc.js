module.exports = {
  parser: "babel-eslint",
  extends: ["airbnb", "prettier"],
  plugins: ["react", "jsx-a11y", "import"],
  env: {
    browser: true
  },
  rules: {
    "react/jsx-filename-extension": [1, { extensions: [".js", ".jsx"] }]
  }
};
