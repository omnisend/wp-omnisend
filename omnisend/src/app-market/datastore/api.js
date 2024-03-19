export async function getPluginData() {
	try {
		const response = await fetch(
			"https://omnisend.github.io/wp-omnisend/plugins.json",
		);

		if (!response.ok) {
			throw new Error("Failed to fetch plugins data");
		}
		const data = await response.json();
		return data;
	} catch (error) {
		console.error("Error fetching plugins data", error);
		return {};
	}
}
