export interface CompanySummary {
    id: number;
    name: string;
    slug: string;
}

export interface User {
    id: number;
    name: string;
    email: string;
    role: 'owner' | 'admin' | 'attendant' | 'viewer';
    status: 'active' | 'inactive' | 'suspended';
    company_id: number;
    company?: CompanySummary | null;
    email_verified_at?: string;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User | null;
    };
    abilities: {
        manageServices: boolean;
        manageAppointments: boolean;
        manageClients: boolean;
        manageAvailability: boolean;
    };
    flash: {
        success?: string;
        error?: string;
    };
};
