export const useCanonical = () => {
    const canonicalUrl = () => {
        return window.location.href;
    };

    const alternateLang = () => {
        console.log(import.meta.env.VITE_APP_URL);
        console.log(window.location.pathname);
        console.log('alternateLang');
    };

    return { alternateLang, canonicalUrl };
};
