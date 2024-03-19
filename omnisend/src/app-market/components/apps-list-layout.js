import {
	Spinner,
	Flex,
	__experimentalText as Text,
	__experimentalSpacer as Spacer,
	__experimentalHeading as Heading
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import AppsList from './apps-list';
import AppsListNotice from './apps-list-notice';

import '../datastore/index';

import { STORE_NAME } from '../datastore/constants';

const AppsListLayout = () => {
	const { apps, categories, isLoading } = useSelect((select) => {
		return {
			apps: select(STORE_NAME).getApps(),
			categories: select(STORE_NAME).getCategories(),
			isLoading: select(STORE_NAME).getIsLoading()
		};
	});

	if (isLoading) {
		return <Spinner />;
	}

	return (
		<Flex className="omnisend-apps-list-page-layout" justify="center" style={{ margin: '40px 0' }}>
			<div>
				<Spacer marginBottom={10}>
					<AppsListNotice />
				</Spacer>

				{categories.map((category) => (
					<div key={category.id}>
						<Spacer marginBottom={15}>
							<AppsList
								apps={apps.filter((app) => app.category_id === category.id)}
								categoryName={category.name}
								categoryDescription={category.description}
							/>
						</Spacer>
					</div>
				))}
			</div>
		</Flex>
	);
};

export default AppsListLayout;
