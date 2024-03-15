export async function getRandomActivity() {
	try {
		const response = await fetch('https://www.boredapi.com/api/activity');

		if (!response.ok) {
			throw new Error('Failed to fetch activity');
		}
		const data = await response.json();

		return data.activity;
	} catch (error) {
		console.error('Error fetching activity:', error);
		return {};
	}
}
