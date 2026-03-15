import type { WP_REST_API_Attachment, WP_REST_API_Post, WP_REST_API_Settings } from 'wp-types';

const APP_HOME = import.meta.env.APP_HOME;
if (!APP_HOME) {
  throw new Error('import.meta.env.APP_HOME is not set.');
}

const BASE_URL = `${APP_HOME.replace(/\/$/, "")}/wp/wp-json`;

const USERNAME = import.meta.env.WP_APPLICATION_USERNAME;
if (!USERNAME) {
  throw new Error('import.meta.env.WP_APPLICATION_USERNAME is not set.');
}

const PASSWORD = import.meta.env.WP_APPLICATION_PASSWORD;
if (!PASSWORD) {
  throw new Error('import.meta.env.WP_APPLICATION_PASSWORD is not set.');
}

const AUTH_HEADER = {
  'Authorization': `Basic ${btoa(`${USERNAME}:${PASSWORD}`)}`,
};

type ACF_Partial = {
  acf: {
    [key: string]: any,
  },
};

export type Post = WP_REST_API_Post & ACF_Partial;

export type Media = WP_REST_API_Attachment & ACF_Partial;

/**
 * Fetch any wp-json endpoint and return the parsed JSON response.
 *
 * @param path  Endpoint path relative to /wp-json, e.g. "wp/v2/posts"
 * @param params  Optional query parameters
 *
 * @example
 * const posts = await wpFetch("wp/v2/posts", { per_page: "100" });
 * const page  = await wpFetch("wp/v2/pages/42");
 */
export async function wpGet<T = unknown>(
  path: string,
  params: Record<string, string> = {}
): Promise<T> {
  const url = new URL(`${BASE_URL}/${path.replace(/^\//, "")}`);

  if (params) {
    for (const [key, value] of Object.entries(params)) {
      url.searchParams.set(key, value);
    }
  }

  const response = await fetch(url.toString(), {
    headers: {
      ...AUTH_HEADER,
    }
  });

  if (!response.ok) {
    throw new Error(
      `wp-api: GET ${url} failed — ${response.status} ${response.statusText}`
    );
  }

  return response.json() as Promise<T>;
}

/**
 * Convenience: fetch ALL items from a paginated wp-json endpoint by
 * walking X-WP-TotalPages and concatenating results.
 *
 * @param path  Endpoint path relative to /wp-json, e.g. "wp/v2/posts"
 * @param params  Optional query parameters (per_page defaults to "100")
 */
export async function wpGetAll<T = unknown>(
  path: string,
  params: Record<string, string> = {}
): Promise<T[]> {
  const firstUrl = new URL(`${BASE_URL}/${path.replace(/^\//, "")}`);

  params.per_page ??= '100';
  params.page = '1';

  for (const [key, value] of Object.entries(params)) {
    firstUrl.searchParams.set(key, value);
  }

  const firstResponse = await fetch(firstUrl.toString(), {
    headers: {
      ...AUTH_HEADER,
    }
  });

  if (!firstResponse.ok) {
    throw new Error(
      `wp-api: GET ${firstUrl} failed — ${firstResponse.status} ${firstResponse.statusText}`
    );
  }

  const totalPages = Number(firstResponse.headers.get("X-WP-TotalPages") ?? 1);
  const firstPage = (await firstResponse.json()) as T[];

  if (totalPages <= 1) return firstPage;

  const remaining = await Promise.all(
    Array.from({ length: totalPages - 1 }, async (_, i) => {
      const url = new URL(firstUrl.toString());

      url.searchParams.set("page", String(i + 2));

      const response = await fetch(url.toString(), {
        headers: {
          ...AUTH_HEADER,
        }
      });

      if (!response.ok) {
        throw new Error(
          `wp-api: GET ${url} failed — ${response.status} ${response.statusText}`
        );
      }

      return response.json() as Promise<T[]>;
    }),
  );

  return [firstPage, ...remaining].flat();
}
