import { stripHtml } from "../../lib/html";
import { getAllPosts, getPostBySlug } from "../../lib/wp-api";
import type { Post } from "../../lib/wp-api";

type TeamMember = {
    name: string,
    class: string,
};

export type Project = {
    id: number,
    slug: string,
    title: string,
    company: string,
    image: number | null,
    video: string | null,
    teamMembers: TeamMember[],
    content: {
        company: string,
        background: string,
        solution: string,
    },
};

export async function getProjectBySlug(slug: string) {
    const post = await getPostBySlug('project', slug);

    const project = mapPostToProject(post);

    return project;
}

export async function getAllProjects() {
    const posts = await getAllPosts('project');

    const projects = posts.map(post => mapPostToProject(post));

    return projects;
}

function mapPostToProject(post: Post): Project {
    return {
        id: post.id,
        slug: post.slug,
        title: stripHtml(post.title.rendered),
        company: post.acf.company,
        image: post.acf.image,
        video: post.acf.video,
        teamMembers: post.acf.team_members,
        content: {
            company: post.acf['content-company'],
            background: post.acf['content-background'],
            solution: post.acf['content-solution'],
        },
    };
}
