export const CLASS_LABELS: Record<string, string> = {
    AD: "Art Direction",
    C: "Copywriting",
    CD: "Communication Design",
    CE: "AI Content Engineering",
    CW: "Copywriting",
    DDS: "Digital Design & Strategy",
    GM: "Growth Marketing",
    PL: "Production Management",
    PR: "Public Relations",
    SK: "Strategic Communication",
    Tutor: "Tutor",
};

export function classLabel(code: string): string {
    return CLASS_LABELS[code] ?? code;
}
