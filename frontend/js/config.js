// Limpa qualquer URL antiga que possa estar no localStorage (legado)
["api_base_url", "api_url"].forEach((key) => {
  const val = localStorage.getItem(key);
  if (val && !val.startsWith(window.location.origin)) {
    localStorage.removeItem(key);
  }
});

const origin = window.location.origin;
const isProduction = origin.includes("railway.app");

let API_BASE, API_URL;

if (isProduction) {
  API_BASE = origin;
  API_URL = `${origin}/api`;
} else {
  const pathParts = window.location.pathname.split("/");
  const projectRoot = pathParts[1] ? `/${pathParts[1]}` : "";
  API_BASE = `${origin}${projectRoot}`;
  API_URL = `${origin}${projectRoot}/api`;
}

export { API_BASE, API_URL };

