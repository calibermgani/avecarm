import { LazyRCMModule } from './lazy-rcm.module';

describe('LazyRCMModule', () => {
  let lazyRCMModule: LazyRCMModule;

  beforeEach(() => {
    lazyRCMModule = new LazyRCMModule();
  });

  it('should create an instance', () => {
    expect(lazyRCMModule).toBeTruthy();
  });
});
